<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Renderer;

class Theme
{
    protected string $name;
    protected string $dir;
    protected $parent;
    protected $templates;

    public function __construct(string $name, string $dir)
    {
        $this->name = $name;
        $this->dir = $dir;
    }

    public function getTemplateDirs(): array
    {
        $dirs = [];
        if ($this->parent) {
            $dirs = $this->parent->getTemplateDirs();
        }

        array_unshift($dirs, $this->dir);

        return $dirs;
    }

    public function setParent(Theme $parent): void
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTemplates(string $type): array
    {
        $templates = [];
        if ($this->parent) {
            $templates = $this->parent->getTemplates($type);
        }

        if (!isset($this->templates[$type])) {
            return $templates;
        }

        return array_replace($templates, $this->templates[$type]);
    }

    public function setTemplates(string $type, array $templates): void
    {
        $this->templates[$type] = $templates;
    }
}
