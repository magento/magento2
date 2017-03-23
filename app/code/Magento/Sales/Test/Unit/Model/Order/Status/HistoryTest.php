<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Status;

use Magento\Sales\Model\Order\Status\History;

/**
 * Class HistoryTest
 */
class HistoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Model\Order | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;

    /**
     * @var History
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface |  \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->order = $this->getMock(\Magento\Sales\Model\Order::class, [], [], '', false);
        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            \Magento\Sales\Model\Order\Status\History::class,
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
        $this->assertEquals(true, $this->model->getIsCustomerNotified());
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
        $config = $this->getMock(\Magento\Sales\Model\Order\Config::class, [], [], '', false);
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
