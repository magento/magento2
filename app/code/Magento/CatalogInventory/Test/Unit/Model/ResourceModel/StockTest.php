<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogInventory\Model\ResourceModel\Stock;

/**
 * Class StockTest
 */
class StockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var Stock
     */
    private $stockModel;

    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->stockModel = (new ObjectManager($this))->getObject(
            Stock::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    public function testLockProductsStock()
    {
        $productIds = [1, 2];
        $websiteId = 1;
        $itemTable = 'item_table';
        $productTable = 'product_table';

        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)->getMock();
        $this->resourceMock->method('getConnection')->will($this->returnValue($connectionMock));

        $this->resourceMock->method('getTableName')
            ->withConsecutive(['cataloginventory_stock_item'], ['catalog_product_entity'])
            ->willReturnOnConsecutiveCalls($itemTable, $productTable);

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectProductsMock = clone $selectMock;

        $selectMock->expects($this->once())->method('forUpdate')->with(true)->willReturnSelf();
        $selectMock->expects($this->once())->method('from')->with(['si' => $itemTable])->willReturnSelf();
        $selectMock->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(['website_id=?', $websiteId], ['product_id IN(?)', $productIds])
            ->willReturnSelf();
        $connectionMock->expects($this->exactly(2))
            ->method('select')
            ->willReturnOnConsecutiveCalls($selectMock, $selectProductsMock);

        $selectProductsMock->expects($this->once())->method('from')->with(['p' => $productTable], [])->willReturnSelf();
        $selectProductsMock->expects($this->once())->method('where')
            ->with('entity_id IN (?)', $productIds)
            ->willReturnSelf();
        $selectProductsMock->expects($this->once())->method('columns')->willReturnSelf();

        $connectionMock->expects($this->once())->method('query')->with($selectMock);
        $connectionMock->expects($this->once())->method('fetchAll')->with($selectProductsMock)->willReturn([]);

        $this->assertEquals([], $this->stockModel->lockProductsStock($productIds, $websiteId));
    }

    public function testLockNoProductsStock()
    {
        $productIds = [];
        $websiteId = 1;

        $this->assertEquals([], $this->stockModel->lockProductsStock($productIds, $websiteId));
    }
}
