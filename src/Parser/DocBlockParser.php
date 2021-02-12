<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sami\Parser;

use gossi\docblock\Docblock;
use gossi\docblock\tags\AbstractTag;
use gossi\docblock\tags\AuthorTag;
use gossi\docblock\tags\ParamTag;
use gossi\docblock\tags\PropertyReadTag;
use gossi\docblock\tags\PropertyTag;
use gossi\docblock\tags\PropertyWriteTag;
use gossi\docblock\tags\ReturnTag;
use gossi\docblock\tags\SeeTag;
use gossi\docblock\tags\TagFactory;
use gossi\docblock\tags\ThrowsTag;
use gossi\docblock\tags\VarTag;
use Sami\Parser\Node\DocBlockNode;

class DocBlockParser
{
    /**
     * @param mixed         $comment
     *
     * @return DocBlockNode
     */
    public function parse(mixed $comment): DocBlockNode
    {
        $docBlock = null;
        $errorMessage = '';

        try {
            $docBlock = new Docblock((string) $comment);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        $result = new DocBlockNode();

        if ($errorMessage) {
            $result->addError($errorMessage);

            return $result;
        }

        $result->setShortDesc($docBlock->getShortDescription());
        $result->setLongDesc($docBlock->getLongDescription());

        foreach ($docBlock->getTags() as $tag) {
            $result->addTag($tag->getTagName(), $this->parseTag($tag));
        }

        return $result;
    }

    public function getTag(string $string): AbstractTag
    {
        return TagFactory::create($string);
    }

    protected function parseTag(AbstractTag $tag): array|string
    {
        return match ($tag::class) {
            VarTag::class, ReturnTag::class => [
                $this->parseHint($tag->getType()),
                $tag->getDescription(),
            ],
            PropertyTag::class, PropertyReadTag::class, PropertyWriteTag::class, ParamTag::class => [
                $this->parseHint($tag->getType()),
                $tag->getVariable(),
                $tag->getDescription(),
            ],
            ThrowsTag::class => [
                $tag->getType(),
                $tag->getDescription(),
            ],
            SeeTag::class => [
                $tag->getReference() . ' ' . $tag->getDescription(),
                $tag->getReference(),
                $tag->getDescription(),
            ],
            default => ltrim((string) $tag, "@{$tag->getTagName()} ")
        };
    }

    protected function parseHint(string $type): array
    {
        $hints = [];
        $rawHints = array_filter(explode('|', $type), 'trim');

        foreach ($rawHints as $hint) {
            if ('[]' == substr($hint, -2)) {
                $hints[] = [substr($hint, 0, -2), true];
            } else {
                $hints[] = [$hint, false];
            }
        }

        return $hints;
    }
}
