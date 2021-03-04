<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model\ResourceModel;

use Magento\AdvancedSearch\Model\ResourceModel\Index;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Index
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceContextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $adapterMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConnectionMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->resourceContextMock = $this->createMock(Context::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->resourceContextMock->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourceConnectionMock);
        $this->adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);

        $indexScopeResolverMock = $this->createMock(
            \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver::class
        );
        $traversableMock = $this->createMock(\Traversable::class);
        $dimensionsMock = $this->createMock(\Magento\Framework\Indexer\MultiDimensionProvider::class);
        $dimensionsMock->method('getIterator')->willReturn($traversableMock);
        $dimensionFactoryMock = $this->createMock(
            \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory::class
        );
        $dimensionFactoryMock->method('create')->willReturn($dimensionsMock);

        $this->model = new Index(
            $this->resourceContextMock,
            $this->storeManagerMock,
            $this->metadataPoolMock,
            'connectionName',
            $indexScopeResolverMock,
            $dimensionFactoryMock
        );
    }

    public function testGetPriceIndexDataUsesFrontendPriceIndexerTable()
    {
        $storeId = 1;
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId)->willReturn($storeMock);

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('union')->willReturnSelf();
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchAll')->with($selectMock)->willReturn([]);

        $this->assertEmpty($this->model->getPriceIndexData([1], $storeId));
    }
}
