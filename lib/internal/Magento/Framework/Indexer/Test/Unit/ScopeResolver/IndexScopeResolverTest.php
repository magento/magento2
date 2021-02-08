<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Test\Unit\ScopeResolver;

use Magento\Framework\Search\Request\Dimension;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver
 */
class IndexScopeResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeResolver;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver
     */
    private $target;

    protected function setUp(): void
    {
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->setMethods(['getTableName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scopeResolver = $this->getMockBuilder(\Magento\Framework\App\ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);

        $this->target = $objectManager->getObject(
            \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver::class,
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
        $scope = $this->getMockBuilder(\Magento\Framework\App\ScopeInterface::class)
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
                'dimensions' => [['first', 10], ['second', 20]],
                // actually you will get exception here thrown in ScopeResolverInterface
                'expected' => 'index_name_first10_second20'
            ],
            [
                'index' => 'index_name',
                'dimensions' => [['second', 10], ['first', 20]],
                'expected' => 'index_name_first20_second10'
            ],
            [
                'index' => 'index_name',
                'dimensions' => [[-1, 10], ['first', 20]],
                'expected' => 'index_name_-110_first20'
            ]
        ];
    }

    /**
     * @param $name
     * @param $value
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createDimension($name, $value)
    {
        $dimension = $this->getMockBuilder(\Magento\Framework\Search\Request\Dimension::class)
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
