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
use Magento\Indexer\Model\ResourceModel\FrontendResource;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataProvider
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rangeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mysqlDataProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $intervalFactoryMock;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);
        $this->sessionMock = $this->getMock(Session::class, [], [], '', false);
        $this->frontendResourceMock = $this->getMock(FrontendResource::class, [], [], '', false);
        $this->adapterMock = $this->getMock(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($this->adapterMock);
        $this->rangeMock = $this->getMock(Range::class, [], [], '', false);
        $this->mysqlDataProviderMock = $this->getMock(DataProviderInterface::class);
        $this->intervalFactoryMock = $this->getMock(IntervalFactory::class, [], [], '', false);

        $this->model = new DataProvider(
            $this->resourceConnectionMock,
            $this->rangeMock,
            $this->sessionMock,
            $this->mysqlDataProviderMock,
            $this->intervalFactoryMock,
            $this->frontendResourceMock
        );
    }

    public function testGetAggregationsUsesFrontendPriceIndexerTable()
    {
        $selectMock = $this->getMock(Select::class, [], [], '', false);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('joinInner')->willReturnSelf();
        $selectMock->expects($this->any())->method('columns')->willReturnSelf();
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $tableMock = $this->getMock(Table::class, [], [], '', false);

        $entityStorageMock = $this->getMock(EntityStorage::class, [], [], '', false);
        $entityStorageMock->expects($this->any())->method('getSource')->willReturn($tableMock);

        // verify that frontend indexer table is used
        $this->frontendResourceMock->expects($this->once())->method('getMainTable');

        $this->model->getAggregations($entityStorageMock);
    }
}
