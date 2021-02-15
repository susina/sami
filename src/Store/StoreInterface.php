<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sami\Store;

use Sami\Project;
use Sami\Reflection\ClassReflection;

interface StoreInterface
{
    public function readClass(Project $project, string $name);

    public function writeClass(Project $project, ClassReflection $class);

    public function removeClass(Project $project, string $name);

    public function readProject(Project $project);

    public function flushProject(Project $project);
}
