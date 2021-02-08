<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Dynamic;

use Magento\CatalogSearch\Model\Adapter\Mysql\Dynamic\DataProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Store\Model\StoreManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataProvider
     */
    private $model;

    /**
     * @var Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionMock;

    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var Range|\PHPUnit\Framework\MockObject\MockObject
     */
    private $rangeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $adapterMock;

    /**
     * @var DataProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mysqlDataProviderMock;

    /**
     * @var IntervalFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $intervalFactoryMock;

    /**
     * @var StoreManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

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
            \Magento\Framework\Search\Request\IndexScopeResolverInterface::class
        );
        $this->dimensionMock = $this->createMock(\Magento\Framework\Indexer\Dimension::class);
        $this->dimensionFactoryMock = $this->createMock(\Magento\Framework\Indexer\DimensionFactory::class);
        $this->dimensionFactoryMock->method('create')->willReturn($this->dimensionMock);
        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
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
