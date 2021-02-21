<?php

namespace Susina\Sami\Tests\Reflection;

use PHPUnit\Framework\TestCase;
use Susina\Sami\Reflection\PropertyReflection;

class PropertyReflectionTest extends TestCase
{
    public function testSetGetModifiers()
    {
        $property = new PropertyReflection('foo', 0);
        $property->setModifiers(0);
        $this->assertTrue($property->isPublic());

        $property->setModifiers(PropertyReflection::MODIFIER_PUBLIC);
        $this->assertTrue($property->isPublic());

        $property->setModifiers(PropertyReflection::MODIFIER_PROTECTED);
        $this->assertTrue($property->isProtected());

        $property->setModifiers(PropertyReflection::MODIFIER_PRIVATE);
        $this->assertTrue($property->isPrivate());

        $property->setModifiers(PropertyReflection::MODIFIER_STATIC);
        $this->assertTrue($property->isPublic());
        $this->assertTrue($property->isStatic());
    }
}
