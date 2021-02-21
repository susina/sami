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

class Version
{
    protected bool $isFrozen;
    protected string $name;
    protected string $longname;

    public function __construct(string $name, string $longname = '')
    {
        $this->name = $name;
        $this->longname = '' === $longname ? $name : $longname;
        $this->isFrozen = false;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLongName(): string
    {
        return $this->longname;
    }

    public function setFrozen(bool $isFrozen): void
    {
        $this->isFrozen = (bool) $isFrozen;
    }

    public function isFrozen(): bool
    {
        return $this->isFrozen;
    }
}
