<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Aggregation;

use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider;
<<<<<<< HEAD
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute;
=======
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\QueryBuilder;
>>>>>>> upstream/2.2-develop
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Ddl\Table;

/**
 * Test for Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
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
    private $adapterMock;

    /**
<<<<<<< HEAD
     * @var \PHPUnit_Framework_MockObject_MockObject|SelectBuilderForAttribute
     */
    private $selectBuilderForAttribute;
=======
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $queryBuilderMock;
>>>>>>> upstream/2.2-develop

    protected function setUp()
    {
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->scopeResolverMock = $this->createMock(ScopeResolverInterface::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($this->adapterMock);
<<<<<<< HEAD
        $this->selectBuilderForAttribute = $this->createMock(SelectBuilderForAttribute::class);
=======
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);

>>>>>>> upstream/2.2-develop
        $this->model = new DataProvider(
            $this->eavConfigMock,
            $this->resourceConnectionMock,
            $this->scopeResolverMock,
            $this->sessionMock,
<<<<<<< HEAD
            $this->selectBuilderForAttribute
=======
            $this->queryBuilderMock
>>>>>>> upstream/2.2-develop
        );
    }

    public function testGetDataSet()
    {
        $storeId = 1;
        $attributeCode = 'my_decimal';

        $scopeMock = $this->createMock(Store::class);
<<<<<<< HEAD
        $scopeMock->expects($this->atLeastOnce())->method('getId')->willReturn($storeId);
        $dimensionMock = $this->createMock(Dimension::class);
        $dimensionMock->expects($this->atLeastOnce())->method('getValue')->willReturn($storeId);
=======
        $scopeMock->expects($this->any())->method('getId')->willReturn($storeId);

        $dimensionMock = $this->createMock(Dimension::class);
        $dimensionMock->expects($this->any())->method('getValue')->willReturn($storeId);

>>>>>>> upstream/2.2-develop
        $this->scopeResolverMock->expects($this->any())->method('getScope')->with($storeId)->willReturn($scopeMock);

        $bucketMock = $this->createMock(BucketInterface::class);
        $bucketMock->expects($this->once())->method('getField')->willReturn($attributeCode);

        $attributeMock = $this->createMock(Attribute::class);
<<<<<<< HEAD
        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')->with(Product::ENTITY, $attributeCode)
            ->willReturn($attributeMock);

        $selectMock = $this->createMock(Select::class);
        $this->adapterMock->expects($this->atLeastOnce())->method('select')->willReturn($selectMock);
=======
        $this->eavConfigMock->expects($this->once())->method('getAttribute')
            ->with(Product::ENTITY, $attributeCode)->willReturn($attributeMock);

>>>>>>> upstream/2.2-develop
        $tableMock = $this->createMock(Table::class);
        $tableMock->expects($this->once())->method('getName')->willReturn('test');

        $this->sessionMock->expects($this->once())->method('getCustomerGroupId')->willReturn(1);

        $this->queryBuilderMock->expects($this->once())->method('build')
            ->with($attributeMock, 'test', $storeId, 1);

        $this->model->getDataSet($bucketMock, ['scope' => $dimensionMock], $tableMock);
    }

    public function testExecute()
    {
<<<<<<< HEAD
        $storeId = 1;
        $attributeCode = 'my_decimal';

        $scopeMock = $this->createMock(Store::class);
        $scopeMock->expects($this->atLeastOnce())->method('getId')->willReturn($storeId);
        $dimensionMock = $this->createMock(Dimension::class);
        $dimensionMock->expects($this->atLeastOnce())->method('getValue')->willReturn($storeId);
        $this->scopeResolverMock->expects($this->atLeastOnce())->method('getScope')->with($storeId)
            ->willReturn($scopeMock);

        $bucketMock = $this->createMock(BucketInterface::class);
        $bucketMock->expects($this->once())->method('getField')->willReturn($attributeCode);
        $attributeMock = $this->createMock(Attribute::class);
        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')->with(Product::ENTITY, $attributeCode)
            ->willReturn($attributeMock);

        $selectMock = $this->createMock(Select::class);
        $this->selectBuilderForAttribute->expects($this->once())->method('build')->willReturn($selectMock);
        $this->adapterMock->expects($this->atLeastOnce())->method('select')->willReturn($selectMock);
        $tableMock = $this->createMock(Table::class);
        $this->model->getDataSet($bucketMock, ['scope' => $dimensionMock], $tableMock);
=======
        $selectMock = $this->createMock(Select::class);
        $this->adapterMock->expects($this->once())->method('fetchAssoc')->with($selectMock);

        $this->model->execute($selectMock);
>>>>>>> upstream/2.2-develop
    }
}
