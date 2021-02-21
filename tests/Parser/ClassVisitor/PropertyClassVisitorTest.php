<?php

/*
 * This file is part of the Sami library.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Tests\Parser\ClassVisitor;

use PHPUnit\Framework\TestCase;
use Susina\Sami\Parser\ClassVisitor\PropertyClassVisitor;

class PropertyClassVisitorTest extends TestCase
{
    public function testAddsProperties()
    {
        $class = $this->getMockBuilder('Susina\Sami\Reflection\ClassReflection')
            ->setMethods(['getTags'])
            ->setConstructorArgs(['Mock', 1])
            ->getMock();

        $property = [
            [
                [],
                'animal',
                'Your favourite animal',
            ],
            [
                [
                    'string',
                    null,
                ],
                'color',
                'Your favourite color',
            ],
            [
                [],
                'enigma',
                null,
            ],
        ];
        $class->expects($this->any())->method('getTags')->with($this->equalTo('property'))->will($this->returnValue($property));

        $context = $this->getMockBuilder('Susina\Sami\Parser\ParserContext')->disableOriginalConstructor()->getMock();

        $visitor = new PropertyClassVisitor($context);
        $visitor->visit($class);

        $this->assertArrayHasKey('color', $class->getProperties());
        $this->assertArrayHasKey('animal', $class->getProperties());
        $this->assertArrayHasKey('enigma', $class->getProperties());
    }
}
