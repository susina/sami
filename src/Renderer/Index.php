<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Renderer;

use Susina\Sami\Project;

class Index implements \Serializable
{
    protected array $classes = [];
    protected array $versions = [];
    protected array $namespaces = [];

    public function __construct(Project $project = null)
    {
        if ($project === null) {
            return;
        }

        foreach ($project->getProjectClasses() as $class) {
            $this->classes[$class->getName()] = $class->getHash();
        }

        foreach ($project->getVersions() as $version) {
            $this->versions[] = (string) $version;
        }

        $this->namespaces = $project->getConfig('simulate_namespaces') ? $project->getSimulatedNamespaces() : $project->getNamespaces();
    }

    public function getVersions(): array
    {
        return $this->versions;
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public function getHash(string $class): string|false
    {
        return $this->classes[$class] ?? false;
    }

    public function serialize(): ?string
    {
        return serialize([$this->classes, $this->versions, $this->namespaces]);
    }

    public function unserialize($data)
    {
        [$this->classes, $this->versions, $this->namespaces] = unserialize($data);
    }
}
