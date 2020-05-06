<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Status;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Status\History;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HistoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Order|MockObject
     */
    protected $order;

    /**
     * @var History
     */
    protected $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->order = $this->createMock(Order::class);
        $this->storeManager = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            History::class,
            ['storeManager' => $this->storeManager]
        );
    }

    public function testSetOrder()
    {
        $storeId = 1;
        $this->order->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->model->setOrder($this->order);
        $this->assertEquals($this->order, $this->model->getOrder());
    }

    public function testSetIsCustomerNotified()
    {
        $this->model->setIsCustomerNotified(true);
        $this->assertTrue($this->model->getIsCustomerNotified());
    }

    public function testSetIsCustomerNotifiedNotApplicable()
    {
        $this->model->setIsCustomerNotified();
        $this->assertEquals($this->model->isCustomerNotificationNotApplicable(), $this->model->getIsCustomerNotified());
    }

    public function testGetStatusLabel()
    {
        $status = 'pending';
        $this->assertNull($this->model->getStatusLabel());
        $this->model->setStatus($status);
        $config = $this->createMock(Config::class);
        $config->expects($this->once())->method('getStatusLabel')->with($status)->willReturn($status);
        $this->order->expects($this->once())->method('getConfig')->willReturn($config);
        $this->model->setOrder($this->order);
        $this->assertEquals($status, $this->model->getStatusLabel());
    }

    public function testGetStoreFromStoreManager()
    {
        $resultStore = 1;
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($resultStore);
        $this->assertEquals($resultStore, $this->model->getStore());
    }

    public function testGetStoreFromOrder()
    {
        $resultStore = 1;
        $this->model->setOrder($this->order);
        $this->order->expects($this->once())->method('getStore')->willReturn($resultStore);
        $this->assertEquals($resultStore, $this->model->getStore());
    }
}
