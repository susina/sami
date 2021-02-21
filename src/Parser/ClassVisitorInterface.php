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

use Susina\Sami\Reflection\ClassReflection;

interface ClassVisitorInterface
{
    public function visit(ClassReflection $class): bool;
}
