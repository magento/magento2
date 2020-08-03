<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Framework\App\Area;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class InvoiceSenderTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testSend()
    {
        Bootstrap::getInstance()
            ->loadArea(Area::AREA_FRONTEND);
        $order = Bootstrap::getObjectManager()
            ->create(Order::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $invoice = Bootstrap::getObjectManager()->create(
            Invoice::class
        );
        $invoice->setOrder($order);
        $invoice->setTotalQty(1);
        $invoice->setBaseSubtotal(50);
        $invoice->setBaseTaxAmount(10);
        $invoice->setBaseShippingAmount(5);
        /** @var InvoiceSender $invoiceSender */
        $invoiceSender = Bootstrap::getObjectManager()
            ->create(InvoiceSender::class);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $invoiceSender->send($invoice, true);

        $this->assertTrue($result);
        $this->assertNotEmpty($invoice->getEmailSent());
        $this->assertEquals($invoice->getBaseSubtotal(), $order->getBaseSubtotal());
        $this->assertEquals($invoice->getBaseTaxAmount(), $order->getBaseTaxAmount());
        $this->assertEquals($invoice->getBaseShippingAmount(), $order->getBaseShippingAmount());
    }
}
