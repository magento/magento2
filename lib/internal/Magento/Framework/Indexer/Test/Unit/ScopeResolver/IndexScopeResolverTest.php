<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Test\Unit\ScopeResolver;

use Magento\Framework\Search\Request\Dimension;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver
 */
class IndexScopeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolver;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver
     */
    private $target;

    protected function setUp()
    {
        $this->resource = $this->getMockBuilder('\Magento\Framework\App\ResourceConnection')
            ->setMethods(['getTableName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();


        $this->scopeResolver = $this->getMockBuilder('Magento\Framework\App\ScopeResolverInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();


        $objectManager = new ObjectManager($this);

        $this->target = $objectManager->getObject(
            '\Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver',
            [
                'resource' => $this->resource,
                'scopeResolver' => $this->scopeResolver
            ]
        );
    }

    /**
     * @param string $indexName
     * @param Dimension[] $dimensions
     * @param string $expected
     * @dataProvider resolveDataProvider
     */
    public function testResolve($indexName, array $dimensions, $expected)
    {
        $dimensions = array_map(
            function ($demension) {
                return $this->createDimension($demension[0], $demension[1]);
            },
            $dimensions
        );
        $scope = $this->getMockBuilder('Magento\Framework\App\ScopeInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $scope->expects($this->any())->method('getId')->willReturn(1);

        $this->resource->expects($this->once())
            ->method('getTableName')
            ->willReturnArgument(0);

        $this->scopeResolver->expects($this->any())->method('getScope')->willReturn($scope);
        $result = $this->target->resolve($indexName, $dimensions);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function resolveDataProvider()
    {
        return [
            [
                'index' => 'some_index',
                'dimensions' => [],
                'expected' => 'some_index'
            ],
            [
                'index' => 'index_name',
                'dimensions' => [['scope', 'name']],
                'expected' => 'index_name_scope1'
            ],
            [
                'index' => 'index_name',
                'dimensions' => [['index', 20]],
                'expected' => 'index_name_index20'
            ],
            [
                'index' => 'index_name',
                'dimensions' => [['dimension', 10], ['dimension', 20]],
                // actually you will get exception here thrown in ScopeResolverInterface
                'expected' => 'index_name_dimension10_dimension20'
            ]
        ];
    }

    /**
     * @param $name
     * @param $value
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createDimension($name, $value)
    {
        $dimension = $this->getMockBuilder('\Magento\Framework\Search\Request\Dimension')
            ->setMethods(['getName', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $dimension->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $dimension->expects($this->any())
            ->method('getValue')
            ->willReturn($value);
        return $dimension;
    }
}
