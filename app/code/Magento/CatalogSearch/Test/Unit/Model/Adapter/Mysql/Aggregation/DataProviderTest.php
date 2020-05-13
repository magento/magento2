<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider;
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\Manager;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 */
class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private $model;

    /**
     * @var MockObject
     */
    private $eavConfigMock;

    /**
     * @var MockObject
     */
    private $sessionMock;

    /**
     * @var MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var MockObject
     */
    private $scopeResolverMock;

    /**
     * @var MockObject
     */
    private $adapterMock;

    /**
     * @var MockObject|SelectBuilderForAttribute
     */
    private $selectBuilderForAttribute;

    /**
     * @var Manager|MockObject
     */
    private $eventManager;

    protected function setUp(): void
    {
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->scopeResolverMock = $this->getMockForAbstractClass(ScopeResolverInterface::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($this->adapterMock);
        $this->selectBuilderForAttribute = $this->createMock(SelectBuilderForAttribute::class);
        $this->eventManager = $this->createMock(Manager::class);
        $this->model = new DataProvider(
            $this->eavConfigMock,
            $this->resourceConnectionMock,
            $this->scopeResolverMock,
            $this->sessionMock,
            $this->selectBuilderForAttribute,
            $this->eventManager
        );
    }

    public function testGetDataSetUsesFrontendPriceIndexerTableIfAttributeIsPrice()
    {
        $storeId = 1;
        $attributeCode = 'price';

        $scopeMock = $this->createMock(Store::class);
        $scopeMock->expects($this->atLeastOnce())->method('getId')->willReturn($storeId);
        $dimensionMock = $this->createMock(Dimension::class);
        $dimensionMock->expects($this->atLeastOnce())->method('getValue')->willReturn($storeId);
        $this->scopeResolverMock->expects($this->any())->method('getScope')->with($storeId)->willReturn($scopeMock);

        $bucketMock = $this->getMockForAbstractClass(BucketInterface::class);
        $bucketMock->expects($this->once())->method('getField')->willReturn($attributeCode);
        $attributeMock = $this->createMock(Attribute::class);
        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')->with(Product::ENTITY, $attributeCode)
            ->willReturn($attributeMock);

        $selectMock = $this->createMock(Select::class);
        $this->adapterMock->expects($this->atLeastOnce())->method('select')->willReturn($selectMock);
        $this->eventManager->expects($this->once())->method('dispatch')->willReturn($selectMock);
        $tableMock = $this->createMock(Table::class);

        $this->model->getDataSet($bucketMock, ['scope' => $dimensionMock], $tableMock);
    }

    public function testGetDataSetUsesFrontendPriceIndexerTableForDecimalAttributes()
    {
        $storeId = 1;
        $attributeCode = 'my_decimal';

        $scopeMock = $this->createMock(Store::class);
        $scopeMock->expects($this->atLeastOnce())->method('getId')->willReturn($storeId);
        $dimensionMock = $this->createMock(Dimension::class);
        $dimensionMock->expects($this->atLeastOnce())->method('getValue')->willReturn($storeId);
        $this->scopeResolverMock->expects($this->atLeastOnce())->method('getScope')->with($storeId)
            ->willReturn($scopeMock);

        $bucketMock = $this->getMockForAbstractClass(BucketInterface::class);
        $bucketMock->expects($this->once())->method('getField')->willReturn($attributeCode);
        $attributeMock = $this->createMock(Attribute::class);
        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')->with(Product::ENTITY, $attributeCode)
            ->willReturn($attributeMock);

        $selectMock = $this->createMock(Select::class);
        $this->selectBuilderForAttribute->expects($this->once())->method('build')->willReturn($selectMock);
        $this->adapterMock->expects($this->atLeastOnce())->method('select')->willReturn($selectMock);
        $this->eventManager->expects($this->once())->method('dispatch')->willReturn($selectMock);
        $tableMock = $this->createMock(Table::class);
        $this->model->getDataSet($bucketMock, ['scope' => $dimensionMock], $tableMock);
    }
}
