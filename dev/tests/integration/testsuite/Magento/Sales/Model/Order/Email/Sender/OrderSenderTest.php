<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Framework\App\Area;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class OrderSenderTest extends TestCase
{
    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->orderSender = Bootstrap::getObjectManager()->create(OrderSender::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testSendNewOrderEmail()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $this->assertEmpty($order->getEmailSent());

        $result = $this->orderSender->send($order);

        $this->assertTrue($result);

        $this->assertNotEmpty($order->getEmailSent());
    }

    /**
     * Verify order will be marked send email on non default store in case default store order email sent is disabled.
     *
     * @magentoDataFixture Magento/Sales/_files/order_fixture_store.php
     * @magentoConfigFixture sales_email/general/async_sending 1
     * @magentoConfigFixture default_store sales_email/order/enabled 0
     * @magentoConfigFixture fixturestore_store sales_email/order/enabled 1
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     */
    public function testSendOrderEmailFromNonDefaultStore()
    {
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->loadByIncrementId('100000004');
        $order->setCustomerEmail('customer@example.com');
        $result = $this->orderSender->send($order);
        $this->assertFalse($result);
        $this->assertTrue($order->getSendEmail());
    }
}
