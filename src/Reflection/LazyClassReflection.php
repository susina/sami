<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Reflection;

class LazyClassReflection extends ClassReflection
{
    protected bool $loaded = false;

    public function __construct(string $name)
    {
        parent::__construct($name, -1);
    }

    public function isProjectClass(): bool
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::isProjectClass();
    }

    public function getShortDesc(): string
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getShortDesc();
    }

    public function setShortDesc($shortDesc): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function getLongDesc(): string
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getLongDesc();
    }

    public function setLongDesc($longDesc): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function getHint(): array
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getHint();
    }

    public function setHint($hint): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function isAbstract(): bool
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::isAbstract();
    }

    public function isFinal(): bool
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::isFinal();
    }

    public function getFile()
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getFile();
    }

    public function setFile($file): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function setModifiers($modifiers): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function addProperty(PropertyReflection $property): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function getProperties(bool $deep = false): array
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getProperties($deep);
    }

    public function setProperties($properties): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function addMethod(MethodReflection $method): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function getParentMethod($name)
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getParentMethod($name);
    }

    public function getMethods(bool $deep = false): array
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getMethods($deep);
    }

    public function setMethods($methods): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function addInterface($interface): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function getInterfaces(bool $deep = false): array
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getInterfaces($deep);
    }

    public function setParent($parent): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function getParent(bool $deep = false): array|ClassReflection|null
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getParent($deep);
    }

    public function setInterface(bool $boolean): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    public function isInterface(): bool
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::isInterface();
    }

    public function isException(): bool
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::isException();
    }

    public function getAliases(): array
    {
        if (false === $this->loaded) {
            $this->load();
        }

        return parent::getAliases();
    }

    public function setAliases($aliases): void
    {
        throw new \LogicException('A LazyClassReflection instance is read-only.');
    }

    protected function load(): void
    {
        $class = $this->project->loadClass($this->name);

        if (null === $class) {
            $this->projectClass = false;
        } else {
            foreach (array_keys(get_class_vars('Susina\Sami\Reflection\ClassReflection')) as $property) {
                $this->$property = $class->$property;
            }
        }

        $this->loaded = true;
    }
}
