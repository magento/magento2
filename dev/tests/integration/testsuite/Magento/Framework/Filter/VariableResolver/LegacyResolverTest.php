<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\VariableResolver;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\VariableResolverInterface;
use PHPUnit\Framework\TestCase;

class LegacyResolverTest extends TestCase
{
    /**
     * @var VariableResolverInterface
     */
    private $variableResolver;

    /**
     * @var Template
     */
    private $filter;

    protected function setUp()
    {
        $objectManager = ObjectManager::getInstance();
        $this->variableResolver = $objectManager->get(LegacyResolver::class);
        $this->filter = $objectManager->get(Template::class);
    }

    /**
     * @dataProvider useCasesProvider
     */
    public function testResolve($value, array $variables, $expected)
    {
        $result = $this->variableResolver->resolve($value, $this->filter, $variables);
        self::assertSame($expected, $result);
    }

    public function useCasesProvider()
    {
        $classStub = new class {
            public function doParams($arg1, $args)
            {
                $result = $arg1;
                foreach ($args as $key => $value) {
                    $result .= $key . '=' . $value . ',';
                }
                return $result;
            }
            public function doThing()
            {
                return 'abc';
            }
            public function getThing()
            {
                return 'abc';
            }
        };
        $dataClassStub = new class extends DataObject {
            public function doThing()
            {
                return 'abc';
            }
            public function doParams($arg1, $args)
            {
                $result = $arg1;
                foreach ($args as $key => $value) {
                    $result .= $key . '=' . $value . ',';
                }
                return $result;
            }
            public function getThing()
            {
                return 'abc';
            }
        };
        $dataClassStub->setData('foo', 'bar');

        return [
            ['', [], null],
            ['foo',['foo' => true], true],
            ['foo',['foo' => 123], 123],
            ['foo',['foo' => 'abc'], 'abc'],
            ['foo',['foo' => false], false],
            ['foo',['foo' => null], null],
            ['foo',['foo' => ''], ''],
            ['foo.bar',['foo' => ['bar' => 123]], 123],
            'nested array' => ['foo.bar.baz',['foo' => ['bar' => ['baz' => 123]]], 123],
            'getter data object with mixed array usage' =>
                ['foo.getBar().baz',['foo' => new DataObject(['bar' => ['baz' => 'abc']])], 'abc'],
            'allow method' => ['foo.doThing()',['foo' => $classStub], 'abc'],
            'allow getter method' => ['foo.getThing()',['foo' => $classStub], 'abc'],
            'arguments for normal class' => [
                'foo.doParams("f", [a:123,b:321])',
                ['foo' => $classStub],
                'fa=123,b=321,'
            ],
            'arguments for normal class with recursive resolution' => [
                'foo.doParams($g.h.i, [a:123,b:321])',
                ['foo' => $classStub, 'g' => ['h' => ['i' => 'abc']]],
                'abca=123,b=321,'
            ],
            'allow normal method for DataObject' => ['foo.doThing()',['foo' => $dataClassStub], 'abc'],
            'allow getter method for DataObject' => ['foo.getThing()',['foo' => $dataClassStub], 'abc'],
            'arguments for DataObject' => [
                'foo.doParams(\'f\', [a:123,b:321])',
                ['foo' => $dataClassStub],
                'fa=123,b=321,'
            ],
            'arguments for DataObject with recursive resolution' => [
                'foo.doParams($g.h.i, [a:123,b:321])',
                ['foo' => $dataClassStub, 'g' => ['h' => ['i' => 'abc']]],
                'abca=123,b=321,'
            ],
        ];
    }
}
