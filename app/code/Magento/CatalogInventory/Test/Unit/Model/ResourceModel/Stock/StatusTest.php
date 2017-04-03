<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\ResourceModel\Stock;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\ResourceModel\FrontendResource;

/**
 * Class StatusTest.
 * Unit test for \Magento\CatalogInventory\Model\ResourceModel\Stock\Status.
 */
class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerStockFrontendResource;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock
     */
    private $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->indexerStockFrontendResource = $this->getMockBuilder(FrontendResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Status::class,
            [
                'indexerStockFrontendResource' => $this->indexerStockFrontendResource
            ]
        );
    }

    public function testGetMainTable()
    {
        $tableName = 'cataloginventory_stock_status';
        $this->indexerStockFrontendResource->expects($this->once())
            ->method('getMainTable')
            ->willReturn($tableName);
        $this->assertEquals($tableName, $this->model->getMainTable());
    }
}
