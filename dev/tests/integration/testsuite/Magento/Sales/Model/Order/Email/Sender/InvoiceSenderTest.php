<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Invoice;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Area;
use PHPUnit\Framework\TestCase;

class InvoiceSenderTest extends TestCase
{
    const NEW_CUSTOMER_EMAIL = 'new.customer@example.com';
    const OLD_CUSTOMER_EMAIL = 'customer@null.com';
    const ORDER_EMAIL = 'customer@null.com';

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerRepository = Bootstrap::getObjectManager()
            ->get(CustomerRepositoryInterface::class);
    }

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

    /**
     * Test that when a customer email is modified, the invoice is sent to the new email
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoAppArea frontend
     */
    public function testSendWhenCustomerEmailWasModified()
    {
        $customer = $this->customerRepository->getById(1);
        $customer->setEmail(self::NEW_CUSTOMER_EMAIL);
        $this->customerRepository->save($customer);

        $order = $this->createOrder();
        $invoice = $this->createInvoice($order);
        $invoiceIdentity = $this->createInvoiceEntity();
        $invoiceSender = $this->createInvoiceSender($invoiceIdentity);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $invoiceSender->send($invoice, true);

        $this->assertEquals(self::NEW_CUSTOMER_EMAIL, $invoiceIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($invoice->getEmailSent());
    }

    /**
     * Test that when a customer email is not modified, the invoice is sent to the old customer email
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoAppArea frontend
     */
    public function testSendWhenCustomerEmailWasNotModified()
    {
        $order = $this->createOrder();
        $invoice = $this->createInvoice($order);
        $invoiceIdentity = $this->createInvoiceEntity();
        $invoiceSender = $this->createInvoiceSender($invoiceIdentity);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $invoiceSender->send($invoice, true);

        $this->assertEquals(self::OLD_CUSTOMER_EMAIL, $invoiceIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($invoice->getEmailSent());
    }

    /**
     * Test that when an order has not customer the invoice is sent to the order email
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoAppArea frontend
     */
    public function testSendWithoutCustomer()
    {
        $order = $this->createOrder();
        $invoice = $this->createInvoice($order);

        /** @var InvoiceIdentity $invoiceIdentity */
        $invoiceIdentity = $this->createInvoiceEntity();
        /** @var InvoiceSender $invoiceSender */
        $invoiceSender = $this->createInvoiceSender($invoiceIdentity);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $invoiceSender->send($invoice, true);

        $this->assertEquals(self::ORDER_EMAIL, $invoiceIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($invoice->getEmailSent());
    }

    private function createInvoice(Order $order): Invoice
    {
        $invoice = Bootstrap::getObjectManager()->create(
            Invoice::class
        );
        $invoice->setOrder($order);

        return $invoice;
    }

    private function createOrder(): Order
    {
        $order = Bootstrap::getObjectManager()
            ->create(Order::class);
        $order->loadByIncrementId('100000001');

        return $order;
    }

    private function createInvoiceEntity(): InvoiceIdentity
    {
        return Bootstrap::getObjectManager()->create(
            InvoiceIdentity::class
        );
    }

    private function createInvoiceSender(InvoiceIdentity $invoiceIdentity): InvoiceSender
    {
        return Bootstrap::getObjectManager()
            ->create(
                InvoiceSender::class,
                [
                    'identityContainer' => $invoiceIdentity,
                ]
            );
    }
}
