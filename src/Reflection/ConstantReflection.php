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
use Stringable;

class ConstantReflection extends Reflection implements Stringable
{
    protected ClassReflection $class;

    public function __toString(): string
    {
        return $this->class.'::'.$this->name;
    }

    public function getClass(): ClassReflection
    {
        return $this->class;
    }

    public function setClass(ClassReflection $class)
    {
        $this->class = $class;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'line' => $this->line,
            'short_desc' => $this->shortDesc,
            'long_desc' => $this->longDesc,
        ];
    }

    public static function fromArray(Project $project, array $array): ConstantReflection
    {
        $constant = new self($array['name'], $array['line']);
        $constant->shortDesc = $array['short_desc'];
        $constant->longDesc = $array['long_desc'];

        return $constant;
    }
}
