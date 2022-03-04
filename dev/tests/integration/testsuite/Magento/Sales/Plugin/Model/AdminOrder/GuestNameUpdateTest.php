<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Plugin\Model\AdminOrder;

use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\AdminOrder\Create as Create;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sales\Plugin\Model\AdminOrder\GuestNameUpdate.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class GuestNameUpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GuestNameUpdate
     */
    private $guestNameUpdate;

    /** @var SessionQuote|MockObject */
    private $session;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->guestNameUpdate = $this->objectManager->get(GuestNameUpdate::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/guest_order_after_edit.php
     * @magentoDbIsolation disabled
     */
    public function testAfterCreateOrder()
    {
        /** @var $subject Create */
        $subject = $this->objectManager->create(Create::class);

        /** @var $guestOrder Order */
        $guestOrder = $this->objectManager->create(Order::class);

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCustomerFirstname', 'getCustomerLastname', 'getCustomerMiddlename'])
            ->getMock();
        $this->orderMock->method('getId')
            ->willReturn(1);
        $this->orderMock->method('getCustomerFirstname')
            ->willReturn('firstname');
        $this->orderMock->method('getCustomerLastname')
            ->willReturn('lastname');
        $this->orderMock->method('getCustomerMiddlename')
            ->willReturn('middlename');

        $this->session = $this->getMockBuilder(SessionQuote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMock();
        $this->session->method('getOrder')
            ->willReturn($this->orderMock);var_dump($this->orderMock->getData());
        $order = $this->guestNameUpdate->afterCreateOrder($subject, $guestOrder);

        self::assertNotNull($order->getCustomerFirstname());
    }


}
