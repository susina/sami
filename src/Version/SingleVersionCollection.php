<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Version;

class SingleVersionCollection extends VersionCollection
{
    public function add($version, $longname = null)
    {
        $countable = is_array($this->versions) || $this->versions instanceof \Countable;

        if ($countable && count($this->versions)) {
            throw new \LogicException('A SingleVersionCollection can only contain one Version');
        }

        parent::add($version, $longname);
    }

    protected function switchVersion(Version $version)
    {
    }
}
