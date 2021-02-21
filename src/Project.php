<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami;

use Susina\Sami\Parser\Parser;
use Susina\Sami\Reflection\ClassReflection;
use Susina\Sami\Reflection\LazyClassReflection;
use Susina\Sami\Renderer\Renderer;
use Susina\Sami\RemoteRepository\AbstractRemoteRepository;
use Susina\Sami\Store\StoreInterface;
use Susina\Sami\Version\SingleVersionCollection;
use Susina\Sami\Version\Version;
use Susina\Sami\Version\VersionCollection;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Project represents an API project.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Project
{
    protected VersionCollection $versions;
    protected StoreInterface $store;
    protected Parser $parser;
    protected ?Renderer $renderer = null;

    /** @var ClassReflection[] */
    protected array $classes;

    protected array $namespaceClasses;
    protected array $namespaceInterfaces;
    protected array $namespaceExceptions;
    protected array $namespaces;
    protected array $simulatedNamespaces;
    protected array $config;
    protected ?Version $version = null;
    protected Filesystem $filesystem;
    protected array $interfaces;

    public function __construct(StoreInterface $store, VersionCollection $versions = null, array $config = [])
    {
        $this->versions = $versions ?? new SingleVersionCollection(new Version('master'));
        $this->store = $store;
        $this->config = array_merge([
            'build_dir' => sys_get_temp_dir().'sami/build',
            'cache_dir' => sys_get_temp_dir().'sami/cache',
            'simulate_namespaces' => false,
            'include_parent_data' => true,
            'theme' => 'default',
        ], $config);
        $this->filesystem = new Filesystem();

        if (count($this->versions) > 1) {
            foreach (['build_dir', 'cache_dir'] as $dir) {
                if (!str_contains($this->config[$dir], '%version%')) {
                    throw new \LogicException(sprintf('The "%s" setting must have the "%%version%%" placeholder as the project has more than one version.', $dir));
                }
            }
        }

        $this->initialize();
    }

    public function setRenderer(Renderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    public function setParser(Parser $parser): void
    {
        $this->parser = $parser;
    }

    public function getConfig(string $name, mixed $default = null): mixed
    {
        return $this->config[$name] ?? $default;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getVersions(): array
    {
        return $this->versions->getVersions();
    }

    public function update(?callable $callback = null, bool $force = false): void
    {
        foreach ($this->versions as $version) {
            $this->switchVersion($version, $callback, $force);
            $this->parseVersion($version, null, $callback, $force);
            $this->renderVersion($version, null, $callback, $force);
        }
    }

    public function parse(callable $callback = null, bool $force = false): void
    {
        $previous = null;
        foreach ($this->versions as $version) {
            $this->switchVersion($version, $callback, $force);

            $this->parseVersion($version, $previous, $callback, $force);

            $previous = $this->getCacheDir();
        }
    }

    public function render(callable $callback = null, bool $force = false): void
    {
        $previous = null;
        foreach ($this->versions as $version) {
            // here, we don't want to flush the parse cache, as we are rendering
            $this->switchVersion($version, $callback, false);

            $this->renderVersion($version, $previous, $callback, $force);

            $previous = $this->getBuildDir();
        }
    }

    public function switchVersion(Version $version, callable $callback = null, bool $force = false): void
    {
        if (null !== $callback) {
            call_user_func($callback, Message::SWITCH_VERSION, $version);
        }

        $this->version = $version;

        $this->initialize();

        if (!$force) {
            $this->read();
        }
    }

    public function hasNamespaces(): bool
    {
        // if there is only one namespace and this is the global one, it means that there is no namespace in the project
        return [''] != array_keys($this->namespaces);
    }

    public function hasNamespace(string $namespace): bool
    {
        return array_key_exists($namespace, $this->namespaces);
    }

    public function getNamespaces(): array
    {
        ksort($this->namespaces);

        return array_keys($this->namespaces);
    }

    public function getSimulatedNamespaces(): array
    {
        ksort($this->simulatedNamespaces);

        return array_keys($this->simulatedNamespaces);
    }

    public function getSimulatedNamespaceAllClasses(string $namespace): array
    {
        if (!isset($this->simulatedNamespaces[$namespace])) {
            return [];
        }

        ksort($this->simulatedNamespaces[$namespace]);

        return $this->simulatedNamespaces[$namespace];
    }

    public function getNamespaceAllClasses(string $namespace): array
    {
        $classes = array_merge(
            $this->getNamespaceExceptions($namespace),
            $this->getNamespaceInterfaces($namespace),
            $this->getNamespaceClasses($namespace)
        );

        ksort($classes);

        return $classes;
    }

    public function getNamespaceExceptions(string $namespace): array
    {
        if (!isset($this->namespaceExceptions[$namespace])) {
            return [];
        }

        ksort($this->namespaceExceptions[$namespace]);

        return $this->namespaceExceptions[$namespace];
    }

    public function getNamespaceClasses(string $namespace): array
    {
        if (!isset($this->namespaceClasses[$namespace])) {
            return [];
        }

        ksort($this->namespaceClasses[$namespace]);

        return $this->namespaceClasses[$namespace];
    }

    public function getNamespaceInterfaces(string $namespace): array
    {
        if (!isset($this->namespaceInterfaces[$namespace])) {
            return [];
        }

        ksort($this->namespaceInterfaces[$namespace]);

        return $this->namespaceInterfaces[$namespace];
    }

    public function getNamespaceSubNamespaces(string $parent): array
    {
        $prefix = strlen($parent) ? ($parent.'\\') : '';
        $len = strlen($prefix);
        $namespaces = [];

        foreach ($this->namespaces as $sub) {
            if (substr($sub, 0, $len) == $prefix
                && !str_contains(substr($sub, $len), '\\')
            ) {
                $namespaces[] = $sub;
            }
        }

        return $namespaces;
    }

    public function addClass(ClassReflection $class): void
    {
        $this->classes[$class->getName()] = $class;
        $class->setProject($this);

        if ($class->isProjectClass()) {
            $this->updateCache($class);
        }
    }

    public function removeClass(ClassReflection $class): void
    {
        unset($this->classes[$class->getName()]);
        unset($this->interfaces[$class->getName()]);
        unset($this->namespaceClasses[$class->getNamespace()][$class->getName()]);
        unset($this->namespaceInterfaces[$class->getNamespace()][$class->getName()]);
        unset($this->namespaceExceptions[$class->getNamespace()][$class->getName()]);
    }

    public function getProjectInterfaces(): array
    {
        $interfaces = [];
        foreach ($this->interfaces as $interface) {
            if ($interface->isProjectClass()) {
                $interfaces[$interface->getName()] = $interface;
            }
        }
        ksort($interfaces);

        return $interfaces;
    }

    public function getProjectClasses(): array
    {
        $classes = [];
        foreach ($this->classes as $name => $class) {
            if ($class->isProjectClass()) {
                $classes[$name] = $class;
            }
        }
        ksort($classes);

        return $classes;
    }

    public function getClass($name): ClassReflection
    {
        $name = ltrim($name, '\\');

        if (isset($this->classes[$name])) {
            return $this->classes[$name];
        }

        $this->addClass($class = new LazyClassReflection($name));

        return $class;
    }

    // this must only be used in LazyClassReflection to get the right values
    public function loadClass(string $name): ?ClassReflection
    {
        $name = ltrim($name, '\\');

        if ($this->getClass($name) instanceof LazyClassReflection) {
            try {
                $this->addClass($this->store->readClass($this, $name));
            } catch (\InvalidArgumentException $e) {
                // probably a PHP built-in class
                return null;
            }
        }

        return $this->classes[$name];
    }

    public function initialize(): void
    {
        $this->namespaces = [];
        $this->simulatedNamespaces = [];
        $this->interfaces = [];
        $this->classes = [];
        $this->namespaceClasses = [];
        $this->namespaceInterfaces = [];
        $this->namespaceExceptions = [];
    }

    public function read(): void
    {
        $this->initialize();

        foreach ($this->store->readProject($this) as $class) {
            $this->addClass($class);
        }
    }

    public function getBuildDir(): string
    {
        return $this->prepareDir($this->config['build_dir']);
    }

    public function getCacheDir(): string
    {
        return $this->prepareDir($this->config['cache_dir']);
    }

    public function flushDir(string $dir): void
    {
        $this->filesystem->remove($dir);
        $this->filesystem->mkdir($dir);
        file_put_contents($dir.'/SAMI_VERSION', Sami::VERSION);
        file_put_contents($dir.'/PROJECT_VERSION', $this->version);
    }

    public function seedCache(string $previous, string $current): void
    {
        $this->filesystem->remove($current);
        $this->filesystem->mirror($previous, $current);
        $this->read();
    }

    public static function isPhpTypeHint(string $hint): bool
    {
        return in_array(strtolower($hint), ['', 'scalar', 'object', 'boolean', 'bool', 'true', 'false', 'int', 'integer', 'array', 'string', 'mixed', 'void', 'null', 'resource', 'double', 'float', 'callable', '$this']);
    }

    protected function updateCache(ClassReflection $class): void
    {
        $name = $class->getName();

        $this->namespaces[$class->getNamespace()] = $class->getNamespace();
        // add sub-namespaces
        $namespace = $class->getNamespace();
        while ($namespace = substr($namespace, 0, (int) strrpos($namespace, '\\'))) {
            $this->namespaces[$namespace] = $namespace;
        }

        if ($class->isException()) {
            $this->namespaceExceptions[$class->getNamespace()][$name] = $class;
        } elseif ($class->isInterface()) {
            $this->namespaceInterfaces[$class->getNamespace()][$name] = $class;
            $this->interfaces[$name] = $class;
        } else {
            $this->namespaceClasses[$class->getNamespace()][$name] = $class;
        }

        if ($this->getConfig('simulate_namespaces')) {
            if (false !== $pos = strrpos($name, '_')) {
                $this->simulatedNamespaces[$namespace = str_replace('_', '\\', substr($name, 0, $pos))][$name] = $class;
                // add sub-namespaces
                while ($namespace = substr($namespace, 0, strrpos($namespace, '\\'))) {
                    if (!isset($this->simulatedNamespaces[$namespace])) {
                        $this->simulatedNamespaces[$namespace] = [];
                    }
                }
            } else {
                $this->simulatedNamespaces[''][$name] = $class;
            }
        }
    }

    protected function prepareDir(string $dir): string
    {
        static $prepared = [];

        $dir = $this->replaceVars($dir);

        if (isset($prepared[$dir])) {
            return $dir;
        }

        $prepared[$dir] = true;

        if (!is_dir($dir)) {
            $this->flushDir($dir);

            return $dir;
        }

        $samiVersion = null;
        if (file_exists($dir.'/SAMI_VERSION')) {
            $samiVersion = file_get_contents($dir.'/SAMI_VERSION');
        }

        if (Sami::VERSION !== $samiVersion) {
            $this->flushDir($dir);
        }

        return $dir;
    }

    protected function replaceVars(string $pattern): string
    {
        return str_replace('%version%', (string) $this->version, $pattern);
    }

    protected function parseVersion(Version $version, ?Version $previous, ?callable $callback = null, bool $force = false): void
    {
        if (null === $this->parser) {
            throw new \LogicException('You must set a parser.');
        }

        if ($version->isFrozen() && count($this->classes) > 0) {
            return;
        }

        if ($force) {
            $this->store->flushProject($this);
        }

        if ($previous && 0 === count($this->classes)) {
            $this->seedCache($previous, $this->getCacheDir());
        }

        $transaction = $this->parser->parse($this, $callback);

        if (null !== $callback) {
            call_user_func($callback, Message::PARSE_VERSION_FINISHED, $transaction);
        }
    }

    protected function renderVersion(Version $version, ?Version $previous, ?callable $callback = null, bool $force = false): void
    {
        if (null === $this->renderer) {
            throw new \LogicException('You must set a renderer.');
        }

        $frozen = $version->isFrozen() && $this->renderer->isRendered($this) && $this->version === file_get_contents($this->getBuildDir().'/PROJECT_VERSION');

        if ($force && !$frozen) {
            $this->flushDir($this->getBuildDir());
        }

        if ($previous && !$this->renderer->isRendered($this)) {
            $this->seedCache($previous, $this->getBuildDir());
        }

        $diff = $this->renderer->render($this, $callback, $force);

        if (null !== $callback) {
            call_user_func($callback, Message::RENDER_VERSION_FINISHED, $diff);
        }
    }

    //@todo never used: remove?
    public function getSourceRoot(): string
    {
        if (!$root = $this->getConfig('source_url')) {
            return '';
        }

        if (str_contains($root, 'github')) {
            return $root.'/tree/'.$this->version;
        }
    }

    public function getViewSourceUrl(string $relativePath, int $line): string
    {
        $remoteRepository = $this->getConfig('remote_repository');

        if ($remoteRepository instanceof AbstractRemoteRepository) {
            return $remoteRepository->getFileUrl($this->version, $relativePath, $line);
        }

        return '';
    }
}
