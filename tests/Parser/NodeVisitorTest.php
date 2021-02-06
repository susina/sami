<?php

namespace Sami\Tests\Parser;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Name\Relative;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use Sami\Parser\DocBlockParser;
use Sami\Parser\Filter\TrueFilter;
use Sami\Parser\NodeVisitor;
use Sami\Parser\ParserContext;
use Sami\Project;
use Sami\Reflection\ClassReflection;
use Sami\Reflection\MethodReflection;
use Sami\Reflection\ParameterReflection;
use Sami\Store\ArrayStore;

/**
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class NodeVisitorTest extends TestCase
{
    /**
     * @dataProvider getMethodTypehints
     */
    public function testMethodTypehints(ClassReflection $classReflection, ClassMethod $method, array $expectedHints)
    {
        $parserContext = new ParserContext(new TrueFilter(), new DocBlockParser(), new Standard());
        $parserContext->enterClass($classReflection);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeVisitor($parserContext));
        $traverser->traverse([$method]);

        /* @var $method MethodReflection */
        $reflMethod = $classReflection->getMethod($method->name);

        $this->assertCount(count($expectedHints), $reflMethod->getParameters());
        foreach ($reflMethod->getParameters() as $paramKey => $parameter) {
            /* @var $parameter ParameterReflection */
            $this->assertArrayHasKey($paramKey, $expectedHints);
            $hint = $parameter->getHint();
            $this->assertCount(count($expectedHints[$paramKey]), $hint);
            foreach ($expectedHints[$paramKey] as $hintKey => $hintVal) {
                $this->assertEquals($hintVal, (string) $hint[$hintKey]);
            }
        }
    }

    /**
     * @dataProvider getMethodReturnTypeHints
     */
    public function testMethodReturnTypeHints(ClassReflection $classReflection, ClassMethod $method, $expectedReturnType)
    {
        $parserContext = new ParserContext(new TrueFilter(), new DocBlockParser(), new Standard());
        $parserContext->enterClass($classReflection);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeVisitor($parserContext));
        $traverser->traverse([$method]);

        /* @var $method MethodReflection */
        $reflMethod = $classReflection->getMethod($method->name);

        $this->assertEquals($expectedReturnType, $reflMethod->getHintAsString());
    }

    /**
     * @return array
     */
    public function getMethodTypehints()
    {
        return [
            'primitive' => $this->getMethodTypehintsPrimiteveParameters(),
            'class' => $this->getMethodTypehintsClassParameters(),
            'subnamespacedclass' => $this->getMethodTypehintsSubNamespacedClassParameters(),
            'docblockclass' => $this->getMethodTypehintsDocblockClassParameters(),
            'docblockmixedclass' => $this->getMethodTypehintsDocblockMixedClassParameters(),
        ];
    }

    /**
     * @return array
     */
    public function getMethodReturnTypeHints()
    {
        return [
            'primitive' => $this->getPrimitiveMethodReturnType(),
            'class' => $this->getClassMethodReturnType(),
            'nullableType' => $this->getNullableMethodReturnType(),
        ];
    }

    private function getPrimitiveMethodReturnType()
    {
        $expectedReturnType = 'string';
        $classReflection = new ClassReflection('C1', 1);
        $method = new ClassMethod('testMethod', [
            'returnType' => 'string'
        ]);

        $classReflection->setMethods([$method]);

        $store = new ArrayStore();
        $store->setClasses([$classReflection]);

        $project = new Project($store);
        $project->loadClass('C1');

        return [
            $classReflection,
            $method,
            $expectedReturnType,
        ];
    }

    private function getClassMethodReturnType()
    {
        $expectedReturnType = 'Class';
        $classReflection = new ClassReflection('C1', 1);
        $method = new ClassMethod('testMethod', [
            'returnType' => new FullyQualified('Test\\Class'),
        ]);

        $classReflection->setMethods([$method]);

        $store = new ArrayStore();
        $store->setClasses([$classReflection]);

        $project = new Project($store);
        $project->loadClass('C1');

        return [
            $classReflection,
            $method,
            $expectedReturnType
        ];
    }

    private function getNullableMethodReturnType()
    {
        $expectedReturnType = 'Class|null';
        $classReflection = new ClassReflection('C1', 1);
        $method = new ClassMethod('testMethod', [
            'returnType' => new NullableType('Test\\Class'),
        ]);

        $classReflection->setMethods([$method]);

        $store = new ArrayStore();
        $store->setClasses([$classReflection]);

        $project = new Project($store);
        $project->loadClass('C1');

        return [
            $classReflection,
            $method,
            $expectedReturnType
        ];
    }


    private function getMethodTypehintsPrimiteveParameters()
    {
        $classReflection = new ClassReflection('C1', 1);
        $method = new ClassMethod('testMethod', [
            'params' => [
                new Param('param1', null, 'int'),
                new Param('param2', null, 'string'),
            ],
        ]);

        $classReflection->setMethods([$method]);
        $store = new ArrayStore();
        $store->setClasses([$classReflection]);

        $project = new Project($store);

        $project->loadClass('C1');

        return [
            $classReflection,
            $method,
            [
                'param1' => ['int'],
                'param2' => ['string'],
            ],
        ];
    }

    private function getMethodTypehintsClassParameters()
    {
        $classReflection = new ClassReflection('C1', 1);
        $paramClassReflection = new ClassReflection("Test\Class", 1);
        $method = new ClassMethod('testMethod', [
            'params' => [
                new Param('param1', null, new FullyQualified('Test\\Class')),
            ],
        ]);

        $classReflection->setMethods([$method]);
        $store = new ArrayStore();
        $store->setClasses([$classReflection, $paramClassReflection]);

        $project = new Project($store);

        $project->loadClass('C1');
        $project->loadClass('Test\\Class');

        return [
            $classReflection,
            $method,
            [
                'param1' => ['Test\Class'],
            ],
        ];
    }

    private function getMethodTypehintsSubNamespacedClassParameters()
    {
        $classReflection = new ClassReflection("Test\Class", 1);
        $paramClassReflection = new ClassReflection("Test\Sub\Class", 1);
        $method = new ClassMethod('testMethod', [
            'params' => [
                new Param('param1', null, new Relative('Sub\\Class')),
            ],
        ]);

        $classReflection->setMethods([$method]);
        $store = new ArrayStore();
        $store->setClasses([$classReflection, $paramClassReflection]);

        $project = new Project($store);

        $project->loadClass('Test\\Class');
        $project->loadClass('Test\\Sub\\Class');

        return [
            $classReflection,
            $method,
            [
                'param1' => ['Sub\Class'],
            ],
        ];
    }

    private function getMethodTypehintsDocblockClassParameters()
    {
        $classReflection = new ClassReflection('C1', 1);
        $paramClassReflection = new ClassReflection("Test\Class", 1);
        $method = new ClassMethod('testMethod', [
            'params' => [
                new Param('param1'),
            ],
        ]);
        $method->setDocComment(new \PhpParser\Comment\Doc('/** @param Test\\Class $param1 */'));

        $classReflection->setMethods([$method]);
        $store = new ArrayStore();
        $store->setClasses([$classReflection, $paramClassReflection]);

        $project = new Project($store);

        $project->loadClass('C1');
        $project->loadClass('Test\\Class');

        return [
            $classReflection,
            $method,
            [
                'param1' => ['Test\Class'],
            ],
        ];
    }

    private function getMethodTypehintsDocblockMixedClassParameters()
    {
        $classReflection = new ClassReflection('C1', 1);
        $paramClassReflection = new ClassReflection("Test\Class", 1);
        $method = new ClassMethod('testMethod', [
            'params' => [
                new Param('param1'),
            ],
        ]);
        $method->setDocComment(new \PhpParser\Comment\Doc('/** @param Test\\Class|string $param1 */'));

        $classReflection->setMethods([$method]);
        $store = new ArrayStore();
        $store->setClasses([$classReflection, $paramClassReflection]);

        $project = new Project($store);

        $project->loadClass('C1');
        $project->loadClass('Test\\Class');

        return [
            $classReflection,
            $method,
            [
                'param1' => ['Test\Class', 'string'],
            ],
        ];
    }
}
