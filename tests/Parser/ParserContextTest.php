<?php

namespace Susina\Sami\Tests\Parser;

use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use PHPUnit\Framework\TestCase;
use Susina\Sami\Parser\DocBlockParser;
use Susina\Sami\Parser\Filter\TrueFilter;
use Susina\Sami\Parser\ParserContext;
use Susina\Sami\Reflection\ClassReflection;

class ParserContextTest extends TestCase
{
    public function testLeaveClassBeforeEnter()
    {
        $filter = new TrueFilter();
        $docBlockParser = new DocBlockParser();
        $prettyPrinter = new PrettyPrinter();

        $context = new ParserContext($filter, $docBlockParser, $prettyPrinter);
        $class = new ClassReflection('C1', 1);

        $context->enterFile(null, null);

        // Leave a class before entering it
        $context->leaveClass();

        // Genuinely enter and leave a class
        $context->enterClass($class);
        $context->leaveClass();

        $classes = $context->leaveFile();

        $this->assertContainsOnlyInstancesOf('Susina\Sami\Reflection\ClassReflection', $classes);
    }
}
