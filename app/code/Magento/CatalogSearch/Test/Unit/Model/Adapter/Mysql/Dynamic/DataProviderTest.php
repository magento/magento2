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
 * Class DataProviderTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataProvider
     */
    private $model;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var Range|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rangeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mysqlDataProviderMock;

    /**
     * @var IntervalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $intervalFactoryMock;

    /**
     * @var StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($this->adapterMock);
        $this->rangeMock = $this->createMock(Range::class);
        $this->mysqlDataProviderMock = $this->createMock(DataProviderInterface::class);
        $this->intervalFactoryMock = $this->createMock(IntervalFactory::class);
        $this->storeManagerMock = $this->createMock(StoreManager::class);

        $this->model = new DataProvider(
            $this->resourceConnectionMock,
            $this->rangeMock,
            $this->sessionMock,
            $this->mysqlDataProviderMock,
            $this->intervalFactoryMock,
            $this->storeManagerMock
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

        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn(42);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->model->getAggregations($entityStorageMock);
    }
}
