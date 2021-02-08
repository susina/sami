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

use Sami\Project;

class ParameterReflection extends Reflection
{
    protected MethodReflection $method;
    protected bool $byRef;
    protected $modifiers;
    protected $default;
    protected $variadic;

    public function __toString(): string
    {
        return $this->method.'#'.$this->name;
    }

    public function getClass(): ClassReflection
    {
        return $this->method->getClass();
    }

    public function setModifiers($modifiers)
    {
        $this->modifiers = $modifiers;
    }

    public function setByRef(bool $boolean): void
    {
        $this->byRef = $boolean;
    }

    public function isByRef(): bool
    {
        return $this->byRef;
    }

    public function setDefault($default): void
    {
        $this->default = $default;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setVariadic($variadic): void
    {
        $this->variadic = $variadic;
    }

    public function getVariadic()
    {
        return $this->variadic;
    }

    public function getMethod(): MethodReflection
    {
        return $this->method;
    }

    public function setMethod(MethodReflection $method): void
    {
        $this->method = $method;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'line' => $this->line,
            'short_desc' => $this->shortDesc,
            'long_desc' => $this->longDesc,
            'hint' => $this->hint,
            'tags' => $this->tags,
            'modifiers' => $this->modifiers,
            'default' => $this->default,
            'variadic' => $this->variadic,
            'is_by_ref' => $this->byRef,
        ];
    }

    public static function fromArray(Project $project, array $array): ParameterReflection
    {
        $parameter = new self($array['name'], $array['line']);
        $parameter->shortDesc = $array['short_desc'];
        $parameter->longDesc = $array['long_desc'];
        $parameter->hint = $array['hint'];
        $parameter->tags = $array['tags'];
        $parameter->modifiers = $array['modifiers'];
        $parameter->default = $array['default'];
        $parameter->variadic = $array['variadic'];
        $parameter->byRef = $array['is_by_ref'];

        return $parameter;
    }
}
