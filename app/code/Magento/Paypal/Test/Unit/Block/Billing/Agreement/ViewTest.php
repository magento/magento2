<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Billing\Agreement;

/**
 * Class ViewTest
 * @package Magento\Paypal\Block\Billing\Agreement
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    /**
     * @var View
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->orderCollectionFactory = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class,
            ['create']
        );
        $this->orderConfig = $this->createMock(\Magento\Sales\Model\Order\Config::class);

        $this->block = $objectManager->getObject(
            \Magento\Paypal\Block\Billing\Agreement\View::class,
            [
                'orderCollectionFactory' => $this->orderCollectionFactory,
                'orderConfig' => $this->orderConfig,
            ]
        );
    }

    public function testGetRelatedOrders()
    {
        $visibleStatuses = [];

        $orderCollection = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Collection::class,
            ['addFieldToSelect', 'addFieldToFilter', 'setOrder']
        );
        $orderCollection->expects($this->at(0))
            ->method('addFieldToSelect')
            ->will($this->returnValue($orderCollection));
        $orderCollection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->will($this->returnValue($orderCollection));
        $orderCollection->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('status', ['in' => $visibleStatuses])
            ->will($this->returnValue($orderCollection));
        $orderCollection->expects($this->at(3))
            ->method('setOrder')
            ->will($this->returnValue($orderCollection));

        $this->orderCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderCollection));
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->will($this->returnValue($visibleStatuses));

        $this->block->getRelatedOrders();
    }
}
