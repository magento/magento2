<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Aggregation\DataProvider;

use Magento\CatalogInventory\Model\Configuration as CatalogInventoryConfiguration;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\QueryBuilder;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;

/**
 *  Test for Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\QueryBuilder.
 */
class QueryBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueryBuilder
     */
    private $model;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $inventoryConfigMock;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->scopeResolverMock = $this->createMock(ScopeResolverInterface::class);
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->inventoryConfigMock = $this->createMock(CatalogInventoryConfiguration::class);

        $this->resourceConnectionMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->adapterMock);

        $this->model = new QueryBuilder(
            $this->resourceConnectionMock,
            $this->scopeResolverMock,
            $this->inventoryConfigMock
        );
    }

    public function testBuildWithPriceAttributeCode()
    {
        $tableName = 'test_table';
        $scope = 1;
        $selectMock = $this->createMock(Select::class);
        $attributeMock = $this->createMock(AbstractAttribute::class);
        $storeMock = $this->createMock(Store::class);

        $this->adapterMock->expects($this->atLeastOnce())->method('select')
            ->willReturn($selectMock);
        $selectMock->expects($this->once())->method('joinInner')
            ->with(['entities' => $tableName], 'main_table.entity_id  = entities.entity_id', []);
        $attributeMock->expects($this->once())->method('getAttributeCode')
            ->willReturn('price');
        $this->scopeResolverMock->expects($this->once())->method('getScope')
            ->with($scope)->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn(1);
        $this->resourceConnectionMock->expects($this->once())->method('getTableName')
            ->with('catalog_product_index_price')->willReturn('catalog_product_index_price');
        $selectMock->expects($this->once())->method('from')
            ->with(['main_table' => 'catalog_product_index_price'], null)
            ->willReturn($selectMock);
        $selectMock->expects($this->once())->method('columns')
            ->with(['value' => 'main_table.min_price'])
            ->willReturn($selectMock);
        $selectMock->expects($this->exactly(2))->method('where')
            ->withConsecutive(
                ['main_table.customer_group_id = ?', 1],
                ['main_table.website_id = ?', 1]
            )->willReturn($selectMock);

        $this->model->build($attributeMock, $tableName, $scope, 1);
    }

    public function testBuildWithNotPriceAttributeCode()
    {
        $tableName = 'test_table';
        $scope = 1;
        $selectMock = $this->createMock(Select::class);
        $attributeMock = $this->createMock(AbstractAttribute::class);
        $storeMock = $this->createMock(Store::class);

        $this->adapterMock->expects($this->atLeastOnce())->method('select')
            ->willReturn($selectMock);
        $selectMock->expects($this->once())->method('joinInner')
            ->with(['entities' => $tableName], 'main_table.entity_id  = entities.entity_id', []);
        $attributeMock->expects($this->once())->method('getBackendType')
            ->willReturn('decimal');
        $this->scopeResolverMock->expects($this->once())->method('getScope')
            ->with($scope)->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->resourceConnectionMock->expects($this->exactly(2))->method('getTableName')
            ->withConsecutive(
                ['catalog_product_index_eav_decimal'],
                ['cataloginventory_stock_status']
            )->willReturnOnConsecutiveCalls(
                'catalog_product_index_eav_decimal',
                'cataloginventory_stock_status'
            );

        $selectMock->expects($this->exactly(2))->method('from')
            ->withConsecutive(
                [
                    ['main_table' => 'catalog_product_index_eav_decimal'],
                    ['main_table.entity_id', 'main_table.value']
                ],
                [['main_table' => $selectMock], ['main_table.value']]
            )
            ->willReturn($selectMock);
        $selectMock->expects($this->once())->method('distinct')->willReturn($selectMock);
        $selectMock->expects($this->once())->method('joinLeft')
            ->with(
                ['stock_index' => 'cataloginventory_stock_status'],
                'main_table.source_id = stock_index.product_id',
                []
            )->willReturn($selectMock);
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn(3);
        $selectMock->expects($this->exactly(3))->method('where')
            ->withConsecutive(
                ['main_table.attribute_id = ?', 3],
                ['main_table.store_id = ? ', 1],
                ['stock_index.stock_status = ?', Stock::STOCK_IN_STOCK]
            )->willReturn($selectMock);
        $this->inventoryConfigMock->expects($this->once())->method('isShowOutOfStock')->with(1)->willReturn(false);

        $this->model->build($attributeMock, $tableName, $scope, 1);
    }
}
