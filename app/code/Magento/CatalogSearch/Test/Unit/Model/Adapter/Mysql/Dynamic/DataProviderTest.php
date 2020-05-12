<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Dynamic;

use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\CatalogSearch\Model\Adapter\Mysql\Dynamic\DataProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 */
class DataProviderTest extends TestCase
{
    /**@var DataProvider */
    private $model;

    /**@var Session|MockObject */
    private $sessionMock;

    /**@var ResourceConnection|MockObject */
    private $resourceConnectionMock;

    /**@var Range|MockObject */
    private $rangeMock;

    /**@var MockObject */
    private $adapterMock;

    /**@var DataProviderInterface|MockObject */
    private $mysqlDataProviderMock;

    /**@var IntervalFactory|MockObject */
    private $intervalFactoryMock;

    /**@var StoreManager|MockObject */
    private $storeManagerMock;

    /**@var IndexScopeResolverInterface|MockObject */
    private $indexScopeResolverMock;

    /**@var Dimension|MockObject */
    private $dimensionMock;

    /**@var DimensionFactory|MockObject */
    private $dimensionFactoryMock;

    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($this->adapterMock);
        $this->rangeMock = $this->createMock(Range::class);
        $this->mysqlDataProviderMock = $this->getMockForAbstractClass(DataProviderInterface::class);
        $this->intervalFactoryMock = $this->createMock(IntervalFactory::class);
        $this->storeManagerMock = $this->createMock(StoreManager::class);
        $this->indexScopeResolverMock = $this->createMock(
            IndexScopeResolverInterface::class
        );
        $this->dimensionMock = $this->createMock(Dimension::class);
        $this->dimensionFactoryMock = $this->createMock(DimensionFactory::class);
        $this->dimensionFactoryMock->method('create')->willReturn($this->dimensionMock);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->method('getId')->willReturn(1);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $this->indexScopeResolverMock->method('resolve')->willReturn('catalog_product_index_price');
        $this->sessionMock->method('getCustomerGroupId')->willReturn(1);

        $this->model = new DataProvider(
            $this->resourceConnectionMock,
            $this->rangeMock,
            $this->sessionMock,
            $this->mysqlDataProviderMock,
            $this->intervalFactoryMock,
            $this->storeManagerMock,
            $this->indexScopeResolverMock,
            $this->dimensionFactoryMock
        );
    }

    public function testGetAggregationsUsesFrontendPriceIndexerTable()
    {
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('columns')->willReturnSelf();
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $tableMock = $this->createMock(Table::class);

        $entityStorageMock = $this->createMock(EntityStorage::class);
        $entityStorageMock->expects($this->any())->method('getSource')->willReturn($tableMock);

        $this->model->getAggregations($entityStorageMock);
    }
}
