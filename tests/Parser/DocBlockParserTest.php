<?php

/*
 * This file is part of the Sami library.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sami\Tests\Parser;

use PHPUnit\Framework\TestCase;
use Sami\Parser\DocBlockParser;
use Sami\Parser\Node\DocBlockNode;

class DocBlockParserTest extends TestCase
{
    /**
     * @dataProvider getParseTests
     */
    public function testParse($comment, $expected)
    {
        $parser = new DocBlockParser();

        $this->assertEquals($this->createDocblock($expected), $parser->parse($comment, $this->getContextMock()));
    }

    public function getParseTests()
    {
        return [
            ['
                /**
                 */
                ',
                [],
            ],
            ['
                /**
                 * The short desc.
                 */
                ',
                ['shortdesc' => 'The short desc.'],
            ],
            ['/** The short desc. */',
                ['shortdesc' => 'The short desc.'],
            ],
            ['
                /**
                 * The short desc on two
                 * lines.
                 */
                ',
                ['shortdesc' => "The short desc on two\nlines."],
            ],
            ['
                /**
                 * The short desc.
                 *
                 * And a long desc.
                 */
                ',
                ['shortdesc' => 'The short desc.', 'longdesc' => 'And a long desc.'],
            ],
            ['
                /**
                 * The short desc on two
                 * lines.
                 *
                 * And a long desc on
                 * several lines too.
                 *
                 * With another paragraph.
                 */
                ',
                ['shortdesc' => "The short desc on two\nlines.", 'longdesc' => "And a long desc on\nseveral lines too.\n\nWith another paragraph."],
            ],
            ['
                /**
                 * The short desc with a @tag embedded. And the short desc continues after dot on same line.
                 */
                ',
                ['shortdesc' => 'The short desc with a @tag embedded. And the short desc continues after dot on same line.'],
            ],
            ['
                /**
                 * @see http://symfony.com/ This is a link description.
                 */
                ',
                ['tags' => ['see' => [['http://symfony.com/ This is a link description.', 'http://symfony.com/', 'This is a link description.']]]],
            ],
            ['
                /**
                 * @author fabien@example.com
                 */
                ',
                ['tags' => ['author' => 'fabien@example.com']],
            ],
            ['
                /**
                 * @author Fabien <fabien@example.com>
                 * @author Thomas <thomas@example.com>
                 */
                ',
                ['tags' => ['author' => ['Fabien <fabien@example.com>', 'Thomas <thomas@example.com>']]],
            ],
            ['
                /**
                 * @var SingleClass|\MultipleClass[] Property Description
                 */
                ',
                [
                    'tags' => [
                        'var' => [ // Array from found tags.
                            [ // First found tag.
                                [['\SingleClass', false], ['\MultipleClass', true]], // Array from data types.
                                'Property Description',
                            ],
                        ],
                    ],
                ],
            ],
            ['
                /**
                 * @param SingleClass|\MultipleClass[] $paramName Param Description
                 */
                ',
                [
                    'tags' => [
                        'param' => [ // Array from found tags.
                            [ // First found tag.
                                [['\SingleClass', false], ['\MultipleClass', true]], // Array from data types.
                                'paramName',
                                'Param Description',
                            ],
                        ],
                    ],
                ],
            ],
            ['
                /**
                 * @throw SingleClass1 Exception Description One
                 * @throws SingleClass2 Exception Description Two
                 */
                ',
                [
                    'tags' => [
                        'throw' => [ // Array from found tags.
                            [ // First found tag.
                                '\SingleClass1',
                                'Exception Description One',
                            ],
                        ],
                        'throws' => [ // Array from found tags.
                            [ // Second found tag.
                                '\SingleClass2',
                                'Exception Description Two',
                            ],
                        ],
                    ],
                ],
            ],
            ['
                /**
                 * @return SingleClass|\MultipleClass[] Return Description
                 */
                ',
                [
                    'tags' => [
                        'return' => [ // Array from found tags.
                            [ // First found tag.
                                [['\SingleClass', false], ['\MultipleClass', true]], // Array from data types.
                                'Return Description',
                            ],
                        ],
                    ],
                ],
            ],
            ['
               /**
                * @author Author Name
                * @covers SomeClass::SomeMethod
                * @deprecated 1.0 for ever
                * @todo Something needs to be done
                * @example Description
                * @link http://www.google.com
                * @method void setInteger(integer $integer)
                * @property-read string $myProperty
                * @property string $myProperty
                * @property-write string $myProperty
                * @see SomeClass::SomeMethod This is a description.
                * @since 1.0.1 First time this was introduced.
                * @source 2 1 Check that ensures lazy counting.
                * @uses MyClass::$items to retrieve the count from.
                * @version 1.0.1
                * @unknown any text
                */
               ',
                [
                    'tags' => [
                        'author' => ['Author Name'],
                        'covers' => ['SomeClass::SomeMethod'],
                        'deprecated' => ['1.0 for ever'],
                        'todo' => ['Something needs to be done'],
                        'example' => ['Description'],
                        'link' => ['http://www.google.com'],
                        'method' => ['void setInteger(integer $integer)'],
                        'property-read' => [   // array of all properties
                            [                  // array of one property
                                [              // array of all typehints of one property
                                    [          // array of one typehint
                                        'string',   // the typehint
                                        null,       // whether or not the typehint is an array
                                    ],
                                ],
                                'myProperty',       // property name
                                '',                  // property description
                            ],
                        ],
                        'property' => [        // see above
                            [
                                [
                                    [
                                        'string',
                                        null,
                                    ],
                                ],
                                'myProperty',
                                '',
                            ],
                        ],
                        'property-write' => [  // see above
                            [
                                [
                                    [
                                        'string',
                                        null,
                                    ],
                                ],
                                'myProperty',
                                '',
                            ],
                        ],
                        'see' => [['SomeClass::SomeMethod This is a description.', 'SomeClass::SomeMethod', 'This is a description.']],
                        'since' => ['1.0.1 First time this was introduced.'],
                        'source' => ['2 1 Check that ensures lazy counting.'],
                        'uses' => ['MyClass::$items to retrieve the count from.'],
                        'version' => ['1.0.1'],
                        'unknown' => ['any text'],
                    ],
                ],
            ],
        ];
    }

    private function createDocblock(array $elements)
    {
        $docblock = new DocBlockNode();
        foreach ($elements as $key => $value) {
            switch ($key) {
                case 'tags':
                    foreach ($value as $tag => $value) {
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                        foreach ($value as $v) {
                            $docblock->addTag($tag, $v);
                        }
                    }
                    break;
                default:
                    $method = 'set'.$key;
                    $docblock->$method($value);
            }
        }

        return $docblock;
    }

    private function getContextMock()
    {
        $contextMock = $this->getMockBuilder('Sami\Parser\ParserContext')->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->once())->method('getNamespace')->will($this->returnValue(''));
        $contextMock->expects($this->once())->method('getAliases')->will($this->returnValue([]));

        return $contextMock;
    }
}
