<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\ResourceModel\Indexer\Stock;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class DefaultStockTest.
 * Unit test for \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock.
 */
class DefaultStockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerStateFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock
     */
    private $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerStateFactory = $this->getMockBuilder(\Magento\Indexer\Model\Indexer\StateFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->model = $objectManager->getObject(
            \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock::class,
            [
                'stateFactory' => $this->indexerStateFactory,
                'resources' => $this->resourceMock
            ]
        );
    }

    public function testGetMainTable()
    {
        $indexerStateModel = $this->getMockBuilder(\Magento\Indexer\Model\Indexer\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn('cataloginventory_stock_status');
        $this->indexerStateFactory->expects($this->once())->method('create')->willReturn($indexerStateModel);
        $indexerStateModel->expects($this->once())
            ->method('loadByIndexer')
            ->with(\Magento\CatalogInventory\Model\Indexer\Stock\Processor::INDEXER_ID)
            ->willReturnSelf();
        $indexerStateModel->expects($this->once())->method('getTableSuffix')->willReturn('');
        $this->assertEquals('cataloginventory_stock_status_replica', $this->model->getMainTable());
    }
}
