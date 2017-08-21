<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Model\ResourceModel\Product\StockStatusBaseSelectProcessor;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class StockStatusBaseSelectProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockConfig;

    /**
     * @var StockStatusBaseSelectProcessor
     */
    private $stockStatusBaseSelectProcessor;

    protected function setUp()
    {
        $this->resource = $this->getMockBuilder(ResourceConnection::class)->disableOriginalConstructor()->getMock();
        $this->select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $this->stockConfig = $this->getMockBuilder(StockConfigurationInterface::class)
            //->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stockStatusBaseSelectProcessor =  (new ObjectManager($this))->getObject(
            StockStatusBaseSelectProcessor::class,
            [
                'resource' => $this->resource,
                'stockConfig' => $this->stockConfig
            ]
        );
    }

    /**
     * @param bool $showOutOfStock
     * @param int $selectJoinCount
     * @param int $selectWhereCount
     * @dataProvider processDataProvider
     */
    public function testProcess($showOutOfStock, $selectJoinCount, $selectWhereCount)
    {
        $tableName = 'table_name';

        $this->resource->expects($this->once())
            ->method('getTableName')
            ->with('cataloginventory_stock_status')
            ->willReturn($tableName);

        $this->stockConfig->expects($this->once())
            ->method('isShowOutOfStock')
            ->willReturn($showOutOfStock);

        $this->select->expects($this->exactly($selectJoinCount))
            ->method('join')
            ->with(
                ['stock' => $tableName],
                sprintf('stock.product_id = %s.entity_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->exactly($selectWhereCount))
            ->method('where')
            ->with('stock.stock_status = ?', Stock::STOCK_IN_STOCK)
            ->willReturnSelf();

        $this->stockStatusBaseSelectProcessor->process($this->select);
    }

    /**
     * Data provider for testProcess
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [true, 0, 0],
            [false, 1, 1]
        ];
    }
}
