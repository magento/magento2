<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout;
use Magento\Sales\Block\Order\Recent;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecentTest extends TestCase
{
    /**
     * @var Recent
     */
    protected $block;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var Session|MockObject
     */
    protected $customerSession;

    /**
     * @var Config|MockObject
     */
    protected $orderConfig;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    protected function setUp(): void
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
            ->willReturn($layout);
        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $statuses = ['pending', 'processing', 'complete'];
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->willReturn($statuses);

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();
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
            ->willReturn($orderCollection);
        $orderCollection->expects($this->at(0))
            ->method('addAttributeToSelect')
            ->with('*')->willReturnSelf();
        $orderCollection->expects($this->at(1))
            ->method('addAttributeToFilter')
            ->with($attribute[0], $customerId)
            ->willReturnSelf();
        $orderCollection->expects($this->at(2))
            ->method('addAttributeToFilter')
            ->with($attribute[1], $storeId)
            ->willReturnSelf();
        $orderCollection->expects($this->at(3))
            ->method('addAttributeToFilter')
            ->with($attribute[2], ['in' => $statuses])->willReturnSelf();
        $orderCollection->expects($this->at(4))
            ->method('addAttributeToSort')
            ->with('created_at', 'desc')->willReturnSelf();
        $orderCollection->expects($this->at(5))
            ->method('setPageSize')
            ->with('5')->willReturnSelf();
        $orderCollection->expects($this->at(6))
            ->method('load')->willReturnSelf();
        $this->block = new Recent(
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
