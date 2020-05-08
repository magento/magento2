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

class StrictResolverTest extends TestCase
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
        $this->variableResolver = $objectManager->get(StrictResolver::class);
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
            public function getThing()
            {
                return 'abc';
            }
        };
        $dataClassStub->setData('foo', 'bar');

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $emailTemplate = $this->createMock(\Magento\Email\Model\Template::class);
        $emailTemplate->method('getUrl')
            ->with($storeMock, 'some path', ['_query' => ['id' => 'abc', 'token' => 'abc'], 'abc' => '1'])
            ->willReturn('a url');

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
            'data object with mixed array usage' =>
                ['foo.bar.baz',['foo' => new DataObject(['bar' => ['baz' => 'abc']])], 'abc'],
            'deny method' => ['foo.doThing()',['foo' => $classStub], null],
            'deny getter method' => ['foo.getThing()',['foo' => $classStub], null],
            'deny normal method for DataObject' => ['foo.doThing()',['foo' => $dataClassStub], null],
            'deny getter method for DataObject' => ['foo.getThing()',['foo' => $dataClassStub], null],
            'convert getter method to getData(foo)' => ['foo.getFoo()',['foo' => $dataClassStub], 'bar'],
            'backwards compatibility exception for getUrl' => [
                'foo.email.getUrl($store,\'some path\',[_query:[id:$foo.bar.baz.bash,token:abc],abc:1])',
                [
                    'store' => $storeMock,
                    'foo' => [
                        'email' => $emailTemplate,
                        'bar' => new DataObject(['baz' => ['bash' => 'abc']])
                    ]
                ],
                'a url'
            ]
        ];
    }
}
