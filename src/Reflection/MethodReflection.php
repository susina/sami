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

use Iterator;
use Susina\Sami\Project;
use Stringable;

class MethodReflection extends Reflection implements Stringable
{
    protected ClassReflection $class;

    /** @var ParameterReflection[] */
    protected array $parameters = [];
    protected bool $byRef = false;
    protected $modifiers;
    protected array $exceptions = [];
    protected array $errors = [];

    public function __toString(): string
    {
        return $this->class.'::'.$this->name;
    }

    public function setByRef(bool|string $boolean): void
    {
        $this->byRef = (bool) $boolean;
    }

    public function isByRef(): bool
    {
        return $this->byRef;
    }

    public function setModifiers($modifiers): void
    {
        // if no modifiers, method is public
        if (0 === ($modifiers & self::VISIBILITY_MODIFER_MASK)) {
            $modifiers |= self::MODIFIER_PUBLIC;
        }

        $this->modifiers = $modifiers;
    }

    public function isPublic(): bool
    {
        return self::MODIFIER_PUBLIC === (self::MODIFIER_PUBLIC & $this->modifiers);
    }

    public function isProtected(): bool
    {
        return self::MODIFIER_PROTECTED === (self::MODIFIER_PROTECTED & $this->modifiers);
    }

    public function isPrivate(): bool
    {
        return self::MODIFIER_PRIVATE === (self::MODIFIER_PRIVATE & $this->modifiers);
    }

    public function isStatic(): bool
    {
        return self::MODIFIER_STATIC === (self::MODIFIER_STATIC & $this->modifiers);
    }

    public function isAbstract(): bool
    {
        return self::MODIFIER_ABSTRACT === (self::MODIFIER_ABSTRACT & $this->modifiers);
    }

    public function isFinal(): bool
    {
        return self::MODIFIER_FINAL === (self::MODIFIER_FINAL & $this->modifiers);
    }

    public function getClass(): ClassReflection
    {
        return $this->class;
    }

    public function setClass(ClassReflection $class): void
    {
        $this->class = $class;
    }

    public function addParameter(ParameterReflection $parameter): void
    {
        $this->parameters[$parameter->getName()] = $parameter;
        $parameter->setMethod($this);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(int|string $name): ?ParameterReflection
    {
        if (ctype_digit((string) $name)) {
            $tmp = array_values($this->parameters);

            return $tmp[$name] ?? null;
        }

        return $this->parameters[$name] ?? null;
    }

    /*
     * Can be any iterator (so that we can lazy-load the parameters)
     */
    public function setParameters(array|Iterator $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function setExceptions(array $exceptions): void
    {
        $this->exceptions = $exceptions;
    }

    public function getExceptions(): array
    {
        $exceptions = [];
        foreach ($this->exceptions as $exception) {
            $exception[0] = $this->class->getProject()->getClass($exception[0]);
            $exceptions[] = $exception;
        }

        return $exceptions;
    }

    public function getRawExceptions(): array
    {
        return $this->exceptions;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors($errors): void
    {
        $this->errors = $errors;
    }

    public function getSourcePath(): string
    {
        return $this->class->getSourcePath($this->line);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'line' => $this->line,
            'short_desc' => $this->shortDesc,
            'long_desc' => $this->longDesc,
            'hint' => $this->hint,
            'hint_desc' => $this->hintDesc,
            'tags' => $this->tags,
            'modifiers' => $this->modifiers,
            'is_by_ref' => $this->byRef,
            'exceptions' => $this->exceptions,
            'errors' => $this->errors,
            'parameters' => array_map(function ($parameter) {
                return $parameter->toArray();
            }, $this->parameters),
        ];
    }

    public static function fromArray(Project $project, array $array): MethodReflection
    {
        $method = new self($array['name'], $array['line']);
        $method->shortDesc = $array['short_desc'];
        $method->longDesc = $array['long_desc'];
        $method->hint = $array['hint'];
        $method->hintDesc = $array['hint_desc'];
        $method->tags = $array['tags'];
        $method->modifiers = $array['modifiers'];
        $method->byRef = $array['is_by_ref'];
        $method->exceptions = $array['exceptions'];
        $method->errors = $array['errors'];

        foreach ($array['parameters'] as $parameter) {
            $method->addParameter(ParameterReflection::fromArray($project, $parameter));
        }

        return $method;
    }
}
