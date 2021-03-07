<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Shipment\Sender;

use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provides tests for shipment email sender.
 */
class EmailSenderTest extends TestCase
{
    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->emailSender = Bootstrap::getObjectManager()->create(EmailSender::class);
    }

    /**
     * Verify shipment will be marked send email on non default store in case default store order email sent is disabled
     *
     * @magentoDataFixture Magento/Sales/_files/order_fixture_store.php
     * @magentoConfigFixture sales_email/general/async_sending 1
     * @magentoConfigFixture default_store sales_email/shipment/enabled 0
     * @magentoConfigFixture fixturestore_store sales_email/shipment/enabled 1
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     */
    public function testSendShipmentEmailFromNonDefaultStore()
    {
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->loadByIncrementId('100000004');
        $order->setCustomerEmail('customer@example.com');
        $shipment = Bootstrap::getObjectManager()->create(Order\Shipment::class);
        $shipment->setOrder($order);
        $result = $this->emailSender->send($order, $shipment);
        $this->assertFalse($result);
        $this->assertTrue($shipment->getSendEmail());
    }
}
