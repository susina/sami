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

use Susina\Sami\Project;
use Stringable;

class PropertyReflection extends Reflection implements Stringable
{
    protected ClassReflection $class;
    protected $modifiers;
    protected $default;
    protected array $errors = [];

    public function __toString(): string
    {
        return $this->class.'::$'.$this->name;
    }

    public function setModifiers($modifiers): void
    {
        // if no modifiers, property is public
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

    public function isFinal(): bool
    {
        return self::MODIFIER_FINAL === (self::MODIFIER_FINAL & $this->modifiers);
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getClass(): ClassReflection
    {
        return $this->class;
    }

    public function setClass(ClassReflection $class): void
    {
        $this->class = $class;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
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
            'default' => $this->default,
            'errors' => $this->errors,
        ];
    }

    public static function fromArray(Project $project, $array): PropertyReflection
    {
        $property = new self($array['name'], $array['line']);
        $property->shortDesc = $array['short_desc'];
        $property->longDesc = $array['long_desc'];
        $property->hint = $array['hint'];
        $property->hintDesc = $array['hint_desc'];
        $property->tags = $array['tags'];
        $property->modifiers = $array['modifiers'];
        $property->default = $array['default'];
        $property->errors = $array['errors'];

        return $property;
    }
}
