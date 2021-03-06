<?php

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Parser\Filter;

use Susina\Sami\Reflection\MethodReflection;
use Susina\Sami\Reflection\PropertyReflection;

class DefaultFilter extends TrueFilter
{
    public function acceptMethod(MethodReflection $method)
    {
        return !$method->isPrivate();
    }

    public function acceptProperty(PropertyReflection $property)
    {
        return !$property->isPrivate();
    }
}
