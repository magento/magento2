<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Order;

use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Layout;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class RecentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Block\Order\Recent
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->context = $this->createMock(Context::class);
        $this->orderCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->customerSession = $this->createPartialMock(Session::class, ['getCustomerId']);
        $this->orderConfig = $this->createPartialMock(
            Config::class,
            ['getVisibleOnFrontStatuses']
        );
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
    }

    public function testConstructMethod()
    {
        $attribute = ['customer_id', 'store_id', 'status'];
        $customerId = 25;
        $storeId = 4;
        $layout = $this->createPartialMock(Layout::class, ['getBlock']);
        $this->context->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layout));
        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));

        $statuses = ['pending', 'processing', 'complete'];
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->will($this->returnValue($statuses));

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(StoreInterface::class)->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->any())->method('getId')->willReturn($storeId);

        $orderCollection = $this->createPartialMock(Collection::class, [
            'addAttributeToSelect',
            'addFieldToFilter',
            'addAttributeToFilter',
            'addAttributeToSort',
            'setPageSize',
            'load'
        ]);
        $this->orderCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderCollection));
        $orderCollection->expects($this->at(0))
            ->method('addAttributeToSelect')
            ->with($this->equalTo('*'))
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(1))
            ->method('addAttributeToFilter')
            ->with($attribute[0], $this->equalTo($customerId))
            ->willReturnSelf();
        $orderCollection->expects($this->at(2))
            ->method('addAttributeToFilter')
            ->with($attribute[1], $this->equalTo($storeId))
            ->willReturnSelf();
        $orderCollection->expects($this->at(3))
            ->method('addAttributeToFilter')
            ->with($attribute[2], $this->equalTo(['in' => $statuses]))
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(4))
            ->method('addAttributeToSort')
            ->with('created_at', 'desc')
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(5))
            ->method('setPageSize')
            ->with('5')
            ->will($this->returnSelf());
        $orderCollection->expects($this->at(6))
            ->method('load')
            ->will($this->returnSelf());
        $this->block = new \Magento\Sales\Block\Order\Recent(
            $this->context,
            $this->orderCollectionFactory,
            $this->customerSession,
            $this->orderConfig,
            [],
            $this->storeManagerMock
        );
        $this->assertEquals($orderCollection, $this->block->getOrders());
    }
}
