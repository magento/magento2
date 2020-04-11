<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model\ResourceModel;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Indexer\MultiDimensionProvider;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
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
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $model;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    /**
     * @var MockObject
     */
    private $resourceContextMock;

    /**
     * @var MockObject
     */
    private $metadataPoolMock;

    /**
     * @var MockObject
     */
    private $adapterMock;

    /**
     * @var MockObject
     */
    private $resourceConnectionMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->resourceContextMock = $this->createMock(Context::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->resourceContextMock
            ->method('getResources')
            ->willReturn($this->resourceConnectionMock);
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->method('getConnection')->willReturn($this->adapterMock);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);

        $indexScopeResolverMock = $this->createMock(
            IndexScopeResolver::class
        );
        $traversableMock = $this->createMock(\Traversable::class);
        $dimensionsMock = $this->createMock(MultiDimensionProvider::class);
        $dimensionsMock->method('getIterator')->willReturn($traversableMock);
        $dimensionFactoryMock = $this->createMock(
            DimensionCollectionFactory::class
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
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getId')->willReturn($storeId);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId)->willReturn($storeMock);

        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $selectMock->method('union')->willReturnSelf();
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchAll')->with($selectMock)->willReturn([]);

        $this->assertEmpty($this->model->getPriceIndexData([1], $storeId));
    }
}
