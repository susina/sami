<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Parser\Node;

class DocBlockNode
{
    protected string $shortDesc = '';
    protected string $longDesc = '';
    protected array $tags = [];
    protected array $errors = [];

    public function addTag(string $key, mixed $value): void
    {
        $this->tags[$key][] = $value;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getOtherTags(): array
    {
        $tags = $this->tags;
        unset($tags['param'], $tags['return'], $tags['var'], $tags['throws']);

        foreach ($tags as $name => $values) {
            foreach ($values as $i => $value) {
                $tags[$name][$i] = is_string($value) ? explode(' ', $value) : $value;
            }
        }

        return $tags;
    }

    public function getTag(string $key): array
    {
        return $this->tags[$key] ?? [];
    }

    public function getShortDesc(): string
    {
        return $this->shortDesc;
    }

    public function getLongDesc(): string
    {
        return $this->longDesc;
    }

    public function setShortDesc(string $shortDesc): void
    {
        $this->shortDesc = $shortDesc;
    }

    public function setLongDesc(string $longDesc): void
    {
        $this->longDesc = $longDesc;
    }

    public function getDesc(): string
    {
        return $this->shortDesc."\n\n".$this->longDesc;
    }

    public function addError($error): void
    {
        $this->errors[] = $error;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
