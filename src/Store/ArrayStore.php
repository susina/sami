<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Store;

use Susina\Sami\Project;
use Susina\Sami\Reflection\ClassReflection;

/**
 * Stores classes in-memory.
 *
 * Mainly useful for unit tests.
 */
class ArrayStore implements StoreInterface
{
    private array $classes = [];

    public function setClasses(array $classes): void
    {
        foreach ($classes as $class) {
            $this->classes[$class->getName()] = $class;
        }
    }

    public function readClass(Project $project, string $name)
    {
        if (!isset($this->classes[$name])) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $name));
        }

        return $this->classes[$name];
    }

    public function removeClass(Project $project, string $name): void
    {
        if (!isset($this->classes[$name])) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $name));
        }

        unset($this->classes[$name]);
    }

    public function writeClass(Project $project, ClassReflection $class): void
    {
        $this->classes[$class->getName()] = $class;
    }

    public function readProject(Project $project): array
    {
        return $this->classes;
    }

    public function flushProject(Project $project): void
    {
        $this->classes = [];
    }
}
