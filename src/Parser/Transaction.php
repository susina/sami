<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Parser;

use Susina\Sami\Project;
use Susina\Sami\Reflection\ClassReflection;

class Transaction
{
    /** @var string[] */
    protected array $hashes = [];

    /** @var string[] */
    protected array $classes = [];

    /** @var string[] */
    protected array $visited = [];

    /** @var string[] */
    protected array $modified = [];

    public function __construct(Project $project)
    {
        foreach ($project->getProjectClasses() as $class) {
            $this->addClass($class);
        }
    }

    public function hasHash(string $hash): bool
    {
        if (!array_key_exists($hash, $this->hashes)) {
            return false;
        }

        $this->visited[$hash] = true;

        return true;
    }

    public function getModifiedClasses(): array
    {
        return $this->modified;
    }

    public function getRemovedClasses(): array
    {
        $classes = [];
        foreach ($this->hashes as $hash => $c) {
            if (!isset($this->visited[$hash])) {
                $classes = array_merge($classes, $c);
            }
        }

        return array_keys($classes);
    }

    public function addClass(ClassReflection $class): void
    {
        $name = $class->getName();
        $hash = $class->getHash();

        if (isset($this->classes[$name])) {
            unset($this->hashes[$this->classes[$name]][$name]);
            if (!$this->hashes[$this->classes[$name]]) {
                unset($this->hashes[$this->classes[$name]]);
            }
        }

        $this->hashes[$hash][$name] = true;
        $this->classes[$name] = $hash;
        $this->modified[] = $name;
        $this->visited[$hash] = true;
    }
}
