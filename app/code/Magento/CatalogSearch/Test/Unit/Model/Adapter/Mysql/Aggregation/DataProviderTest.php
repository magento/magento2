<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Aggregation;

use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Indexer\Model\ResourceModel\FrontendResource;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Ddl\Table;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataProvider
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

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
    private $scopeResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendResourceStockMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\StateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateFactoryMock;

    protected function setUp()
    {
        $this->eavConfigMock = $this->getMock(Config::class, [], [], '', false);
        $this->resourceConnectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);
        $this->scopeResolverMock = $this->getMock(ScopeResolverInterface::class);
        $this->sessionMock = $this->getMock(Session::class, [], [], '', false);
        $this->frontendResourceMock = $this->getMock(FrontendResource::class, [], [], '', false);
        $this->frontendResourceStockMock = $this->getMock(FrontendResource::class, [], [], '', false);
        $this->adapterMock = $this->getMock(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($this->adapterMock);
        $this->stateFactoryMock = $this->getMockBuilder(\Magento\Indexer\Model\Indexer\StateFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->model = new DataProvider(
            $this->eavConfigMock,
            $this->resourceConnectionMock,
            $this->scopeResolverMock,
            $this->sessionMock,
            $this->frontendResourceMock,
            $this->stateFactoryMock,
            $this->frontendResourceStockMock
        );
    }

    public function testGetDataSetUsesFrontendPriceIndexerTableIfAttributeIsPrice()
    {
        $storeId = 1;
        $attributeCode = 'price';

        $scopeMock = $this->getMock(Store::class, [], [], '', false);
        $scopeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $dimensionMock = $this->getMock(Dimension::class, [], [], '', false);
        $dimensionMock->expects($this->any())->method('getValue')->willReturn($storeId);
        $this->scopeResolverMock->expects($this->any())->method('getScope')->with($storeId)->willReturn($scopeMock);

        $bucketMock = $this->getMock(BucketInterface::class);
        $bucketMock->expects($this->once())->method('getField')->willReturn($attributeCode);
        $attributeMock = $this->getMock(Attribute::class, [], [], '', false);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')->with(Product::ENTITY, $attributeCode)
            ->willReturn($attributeMock);

        $selectMock = $this->getMock(Select::class, [], [], '', false);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('columns')->willReturnSelf();
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $tableMock = $this->getMock(Table::class);

        // verify that frontend indexer table is used
        $this->frontendResourceMock->expects($this->once())->method('getMainTable');

        $this->model->getDataSet($bucketMock, ['scope' => $dimensionMock], $tableMock);
    }

    public function testGetDataSetUsesFrontendPriceIndexerTableForDecimalAttributes()
    {
        $storeId = 1;
        $attributeCode = 'my_decimal';
        $stateMock = $this->getMock(\Magento\Indexer\Model\Indexer\State::class, [], [], '', false);
        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);
        $stateMock->expects($this->once())
            ->method('loadByIndexer')
            ->with(\Magento\Catalog\Model\Indexer\Product\Eav\Processor::INDEXER_ID)
            ->willReturnSelf();
        // verify that frontend indexer table is used
        $stateMock->expects($this->once())->method('getTableSuffix')->willReturn('_replica');

        $scopeMock = $this->getMock(Store::class, [], [], '', false);
        $scopeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $dimensionMock = $this->getMock(Dimension::class, [], [], '', false);
        $dimensionMock->expects($this->any())->method('getValue')->willReturn($storeId);
        $this->scopeResolverMock->expects($this->any())->method('getScope')->with($storeId)->willReturn($scopeMock);

        $bucketMock = $this->getMock(BucketInterface::class);
        $bucketMock->expects($this->once())->method('getField')->willReturn($attributeCode);
        $attributeMock = $this->getMock(Attribute::class, [], [], '', false);
        $attributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);
        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')->with(Product::ENTITY, $attributeCode)
            ->willReturn($attributeMock);

        $selectMock = $this->getMock(Select::class, [], [], '', false);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('distinct')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $selectMock->expects($this->any())->method('columns')->willReturnSelf();
        $selectMock->expects($this->any())->method('joinLeft')->willReturnSelf();
        $selectMock->expects($this->any())->method('group')->willReturnSelf();
        $this->adapterMock->expects($this->any())->method('select')->willReturn($selectMock);
        $tableMock = $this->getMock(Table::class);
        $this->model->getDataSet($bucketMock, ['scope' => $dimensionMock], $tableMock);
    }
}
