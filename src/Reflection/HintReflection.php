<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sami\Reflection;

//@todo refactor to accept only one type of name
class HintReflection implements \Stringable
{
    protected string|ClassReflection $name;
    protected bool $array;

    public function __construct(string|ClassReflection $name, bool $array)
    {
        $this->name = $name;
        $this->array = $array;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getName(): string|ClassReflection
    {
        return $this->name;
    }

    public function setName(string|ClassReflection $name): void
    {
        $this->name = $name;
    }

    public function isClass(): bool
    {
        return $this->name instanceof ClassReflection;
    }

    public function isArray(): bool
    {
        return $this->array;
    }

    public function setArray(bool $boolean): void
    {
        $this->array = $boolean;
    }
}
