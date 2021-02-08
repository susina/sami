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

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use Sami\Parser\Node\DocBlockNode;

class DocBlockParser
{
    /**
     * @param mixed         $comment
     * @param ParserContext $context
     *
     * @return DocBlockNode
     */
    public function parse(mixed $comment, ParserContext $context): DocBlockNode
    {
        $docBlock = null;
        $errorMessage = '';

        try {
            $docBlockContext = new DocBlock\Context($context->getNamespace(), $context->getAliases() ?: []);
            $docBlock = new DocBlock((string) $comment, $docBlockContext);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        $result = new DocBlockNode();

        if ($errorMessage) {
            $result->addError($errorMessage);

            return $result;
        }

        $result->setShortDesc($docBlock->getShortDescription());
        $result->setLongDesc((string) $docBlock->getLongDescription());

        foreach ($docBlock->getTags() as $tag) {
            $result->addTag($tag->getName(), $this->parseTag($tag));
        }

        return $result;
    }

    public function getTag(string $string): Tag
    {
        return Tag::createInstance($string);
    }

    protected function parseTag(DocBlock\Tag $tag): array|string
    {
        return match (substr(get_class($tag), 38)) {
            'VarTag', 'ReturnTag' => [
                $this->parseHint($tag->getTypes()),
                $tag->getDescription(),
            ],
            'PropertyTag', 'PropertyReadTag', 'PropertyWriteTag', 'ParamTag' => [
                $this->parseHint($tag->getTypes()),
                ltrim($tag->getVariableName(), '$'),
                $tag->getDescription(),
            ],
            'ThrowsTag' => [
                $tag->getType(),
                $tag->getDescription(),
            ],
            'SeeTag' => [
                $tag->getContent(),
                $tag->getReference(),
                $tag->getDescription(),
            ],
            default => $tag->getContent(),
        };
    }

    protected function parseHint(array $rawHints): array
    {
        $hints = [];
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
