<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit\ScopeResolver;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver
 */
class IndexScopeResolverTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|MockObject
     */
    protected $scopeResolver;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     */
    private $target;

    protected function setUp(): void
    {
        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->onlyMethods(['getTableName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);

        $this->target = $objectManager->getObject(
            IndexScopeResolver::class,
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
        $scope = $this->getMockBuilder(ScopeInterface::class)
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
    public static function resolveDataProvider()
    {
        return [
            [
                'indexName' => 'some_index',
                'dimensions' => [],
                'expected' => 'some_index'
            ],
            [
                'indexName' => 'index_name',
                'dimensions' => [['scope', 'name']],
                'expected' => 'index_name_scope1'
            ],
            [
                'indexName' => 'index_name',
                'dimensions' => [['index', 20]],
                'expected' => 'index_name_index20'
            ],
            [
                'indexName' => 'index_name',
                'dimensions' => [['first', 10], ['second', 20]],
                // actually you will get exception here thrown in ScopeResolverInterface
                'expected' => 'index_name_first10_second20'
            ],
            [
                'indexName' => 'index_name',
                'dimensions' => [['second', 10], ['first', 20]],
                'expected' => 'index_name_first20_second10'
            ],
            [
                'indexName' => 'index_name',
                'dimensions' => [[-1, 10], ['first', 20]],
                'expected' => 'index_name_-110_first20'
            ]
        ];
    }

    /**
     * @param $name
     * @param $value
     * @return MockObject
     */
    private function createDimension($name, $value)
    {
        $dimension = $this->getMockBuilder(Dimension::class)
            ->onlyMethods(['getName', 'getValue'])
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
