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
     * @var \Magento\CatalogInventory\Helper\Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockHelper;

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
        $this->stockHelper = $this->getMock('Magento\CatalogInventory\Helper\Stock', [], [], '', false);

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
            'Magento\CatalogInventory\Observer\AddStockStatusToCollectionObserver',
            [
                'stockHelper' => $this->stockHelper,
            ]
        );
    }

    public function testAddStockStatusToCollection()
    {
        $productCollection = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($productCollection));

        $this->stockHelper->expects($this->once())
            ->method('addStockStatusToProducts')
            ->with($productCollection)
            ->will($this->returnSelf());

        $this->observer->execute($this->eventObserver);
    }
}
