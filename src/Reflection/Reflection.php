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

abstract class Reflection
{
    const MODIFIER_PUBLIC = 1;
    const MODIFIER_PROTECTED = 2;
    const MODIFIER_PRIVATE = 4;
    const MODIFIER_STATIC = 8;
    const MODIFIER_ABSTRACT = 16;
    const MODIFIER_FINAL = 32;
    const VISIBILITY_MODIFER_MASK = 7; // 1 | 2 | 4

    protected string $name;
    protected int $line;
    protected ?string $shortDesc = '';
    protected string $longDesc = '';
    protected $hint;
    protected string $hintDesc = '';
    protected array $tags;
    protected $docComment;
    protected $see = [];

    public function __construct(string $name, int $line)
    {
        $this->name = $name;
        $this->line = $line;
        $this->tags = [];
    }

    abstract public function getClass();

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function setLine(int $line): void
    {
        $this->line = $line;
    }

    public function getShortDesc(): string
    {
        return $this->shortDesc;
    }

    public function setShortDesc(?string $shortDesc): void
    {
        $this->shortDesc = $shortDesc;
    }

    public function getLongDesc(): string
    {
        return $this->longDesc;
    }

    public function setLongDesc(string $longDesc): void
    {
        $this->longDesc = $longDesc;
    }

    public function getHint(): array
    {
        if (!$this->hint) {
            return [];
        }

        $hints = [];
        $project = $this->getClass()->getProject();
        foreach ($this->hint as $hint) {
            $hints[] = new HintReflection(Project::isPhpTypeHint($hint[0]) ? $hint[0] : $project->getClass($hint[0]), $hint[1]);
        }

        return $hints;
    }

    public function getHintAsString(): string
    {
        $str = [];
        foreach ($this->getHint() as $hint) {
            $str[] = ($hint->isClass() ? $hint->getName()->getShortName() : $hint->getName()).($hint->isArray() ? '[]' : '');
        }

        return implode('|', $str);
    }

    public function hasHint(): bool
    {
        return $this->hint ? true : false;
    }

    public function setHint($hint)
    {
        $this->hint = $hint;
    }

    public function getRawHint()
    {
        return $this->hint;
    }

    public function setHintDesc(string $desc): void
    {
        $this->hintDesc = $desc;
    }

    public function getHintDesc(): string
    {
        return $this->hintDesc;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getTags(string $name): array
    {
        return $this->tags[$name] ?? [];
    }

    public function getDeprecated(): array
    {
        return $this->getTags('deprecated');
    }

    public function getTodo(): array
    {
        return $this->getTags('todo');
    }

    // not serialized as it is only useful when parsing
    public function setDocComment($comment)
    {
        $this->docComment = $comment;
    }

    public function getDocComment()
    {
        return $this->docComment;
    }

    /**
     * @return array
     */
    public function getSee(): array
    {
        $see = [];
        /* @var $project Project */
        $project = $this->getClass()->getProject();

        foreach ($this->see as $seeElem) {
            if ($seeElem[3]) {
                $seeElem = $this->prepareMethodSee($seeElem);
            } elseif ($seeElem[2]) {
                $seeElem[2] = Project::isPhpTypeHint($seeElem[2]) ? $seeElem[2] : $project->getClass($seeElem[2]);
            }

            $see[] = $seeElem;
        }

        return $see;
    }

    /**
     * @param array $see
     */
    public function setSee(array $see)
    {
        $this->see = $see;
    }

    private function prepareMethodSee(array $seeElem): array
    {
        /* @var $project Project */
        $project = $this->getClass()->getProject();

        $class = $project->getClass($seeElem[2]);
        $method = $class->getMethod($seeElem[3]);

        if ($method) {
            $seeElem[2] = false;
            $seeElem[3] = $method;
        } else {
            $seeElem[2] = false;
            $seeElem[3] = false;
        }

        return $seeElem;
    }
}
