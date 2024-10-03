<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Invoice;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\ErrorLog\Logger;

/**
 * Checks the sending of order invoice email to the customer.
 *
 * @see \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceSenderTest extends TestCase
{
    private const NEW_CUSTOMER_EMAIL = 'new.customer@example.com';
    private const OLD_CUSTOMER_EMAIL = 'customer@example.com';
    private const ORDER_EMAIL = 'customer@example.com';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var InvoiceSender */
    private $invoiceSender;

    /** @var TransportBuilderMock */
    private $transportBuilderMock;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var InvoiceInterfaceFactory */
    private $invoiceFactory;

    /** @var InvoiceIdentity */
    private $invoiceIdentity;

    /** @var Logger */
    private $logger;

    /** @var int */
    private $minErrorDefaultValue;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->invoiceSender = $this->objectManager->get(InvoiceSender::class);
        $this->transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->invoiceFactory = $this->objectManager->get(InvoiceInterfaceFactory::class);
        $this->invoiceIdentity = $this->objectManager->get(InvoiceIdentity::class);
        $this->logger = $this->objectManager->get(Logger::class);

        $reflection = new \ReflectionClass(get_class($this->logger));
        $reflectionProperty = $reflection->getProperty('minimumErrorLevel');
        $reflectionProperty->setAccessible(true);
        $this->minErrorDefaultValue = $reflectionProperty->getValue($this->logger);
        $reflectionProperty->setValue($this->logger, 400);
        $this->logger->clearMessages();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testSend(): void
    {
        $order = $this->getOrder('100000001');
        $order->setCustomerEmail('customer@example.com');
        $invoice = $this->createInvoice($order);
        $invoice->setTotalQty(1);
        $invoice->setBaseSubtotal(50);
        $invoice->setBaseTaxAmount(10);
        $invoice->setBaseShippingAmount(5);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $this->invoiceSender->send($invoice, true);
        $this->assertEmpty($this->logger->getMessages());

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
     * @return void
     */
    public function testSendWhenCustomerEmailWasModified(): void
    {
        $customer = $this->customerRepository->getById(1);
        $customer->setEmail(self::NEW_CUSTOMER_EMAIL);
        $this->customerRepository->save($customer);
        $order = $this->getOrder('100000001');
        $invoice = $this->createInvoice($order);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $this->invoiceSender->send($invoice, true);
        $this->assertEmpty($this->logger->getMessages());

        $this->assertEquals(self::NEW_CUSTOMER_EMAIL, $this->invoiceIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($invoice->getEmailSent());
    }

    /**
     * Test that when a customer email is not modified, the invoice is sent to the old customer email
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testSendWhenCustomerEmailWasNotModified(): void
    {
        $order = $this->getOrder('100000001');
        $invoice = $this->createInvoice($order);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $this->invoiceSender->send($invoice, true);
        $this->assertEmpty($this->logger->getMessages());

        $this->assertEquals(self::OLD_CUSTOMER_EMAIL, $this->invoiceIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($invoice->getEmailSent());
    }

    /**
     * Test that when an order has not customer the invoice is sent to the order email
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testSendWithoutCustomer(): void
    {
        $order = $this->getOrder('100000001');
        $invoice = $this->createInvoice($order);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $this->invoiceSender->send($invoice, true);
        $this->assertEmpty($this->logger->getMessages());

        $this->assertEquals(self::ORDER_EMAIL, $this->invoiceIdentity->getCustomerEmail());
        $this->assertTrue($result);
        $this->assertNotEmpty($invoice->getEmailSent());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoConfigFixture default/sales_email/general/async_sending 1
     * @return void
     */
    public function testSendWithAsyncSendingEnabled(): void
    {
        $order = $this->getOrder('100000001');
        /** @var Invoice $invoice */
        $invoice = $order->getInvoiceCollection()
            ->addAttributeToFilter(InvoiceInterface::ORDER_ID, $order->getID())
            ->getFirstItem();
        $result = $this->invoiceSender->send($invoice);
        $this->assertEmpty($this->logger->getMessages());
        $this->assertFalse($result);
        $invoice = $order->getInvoiceCollection()->clear()->getFirstItem();
        $this->assertEmpty($invoice->getEmailSent());
        $this->assertEquals('1', $invoice->getSendEmail());
        $this->assertNull(
            $this->transportBuilderMock->getSentMessage(),
            'The message is not expected to be received.'
        );
    }

    /**
     * Verify invoice will be marked send email on non default store in case default store email sent is disabled.
     *
     * @magentoDataFixture Magento/Sales/_files/order_fixture_store.php
     * @magentoConfigFixture sales_email/general/async_sending 1
     * @magentoConfigFixture default_store sales_email/invoice/enabled 0
     * @magentoConfigFixture fixturestore_store sales_email/invoice/enabled 1
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     */
    public function testSendInvoiceEmailFromNonDefaultStore()
    {
        $order = Bootstrap::getObjectManager()->create(Order::class);
        $order->loadByIncrementId('100000004');
        $order->setCustomerEmail('customer@example.com');
        $invoice = $this->createInvoice($order);
        $result = $this->invoiceSender->send($invoice);
        $this->assertEmpty($this->logger->getMessages());
        $this->assertFalse($result);
        $this->assertTrue($invoice->getSendEmail());
    }

    /**
     * Create invoice and set order
     *
     * @param OrderInterface $order
     * @return InvoiceInterface
     */
    private function createInvoice(OrderInterface $order): InvoiceInterface
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoiceFactory->create();
        $invoice->setOrder($order);

        return $invoice;
    }

    /**
     * Get order by increment_id
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        return $this->orderFactory->create()->loadByIncrementId($incrementId);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $reflectionProperty = new \ReflectionProperty(get_class($this->logger), 'minimumErrorLevel');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->logger, $this->minErrorDefaultValue);
    }
}
