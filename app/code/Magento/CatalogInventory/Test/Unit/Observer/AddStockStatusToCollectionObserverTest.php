<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\AddStockStatusToCollectionObserver;

class AddStockStatusToCollectionObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddStockStatusToCollectionObserver
     */
    protected $observer;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserver;

    protected function setUp()
    {
        $this->event = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getCollection'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));

        $this->observer = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\CatalogInventory\Observer\AddStockStatusToCollectionObserver'
        );
    }

    public function testAddStockStatusToCollection()
    {
        $resourceMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->once())
            ->method('getTable')
            ->with('cataloginventory_stock_status')
            ->willReturnArgument(0);

        $selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->once())
            ->method('join')
            ->with(
                ['css' => 'cataloginventory_stock_status'],
                'e.entity_id = css.product_id AND css.website_id = 1 AND css.stock_id = 1',
                ['is_salable' => 'css.stock_status']
            )->willReturnSelf();

        $productCollection = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->any())
            ->method('getSelect')
            ->willReturn($selectMock);
        $productCollection->expects($this->once())
            ->method('getResource')
            ->willReturn($resourceMock);

        $this->event->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($productCollection));

        $this->observer->execute($this->eventObserver);
    }
}
