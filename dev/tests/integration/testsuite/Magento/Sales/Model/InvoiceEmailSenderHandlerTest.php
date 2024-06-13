<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceSearchResultInterface;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Spi\InvoiceResourceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * Checks sending emails to customers after creation/modification of invoice.
 *
 * @see \Magento\Sales\Model\EmailSenderHandler
 */
class InvoiceEmailSenderHandlerTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var InvoiceSearchResultInterface */
    private $entityCollection;

    /** @var EmailSenderHandler */
    private $emailSenderHandler;

    /** @var InvoiceIdentity */
    private $invoiceIdentity;

    /** @var InvoiceSender */
    private $invoiceSender;

    /** @var InvoiceResourceInterface */
    private $entityResource;

    /** @var TransportBuilderMock */
    private $transportBuilderMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->invoiceIdentity = $this->objectManager->get(InvoiceIdentity::class);
        $this->invoiceSender = $this->objectManager->get(InvoiceSender::class);
        $this->entityResource = $this->objectManager->get(InvoiceResourceInterface::class);
        $this->entityCollection = $this->objectManager->create(InvoiceSearchResultInterface::class);
        $this->emailSenderHandler = $this->objectManager->create(
            EmailSenderHandler::class,
            [
                'emailSender'       => $this->invoiceSender,
                'entityResource'    => $this->entityResource,
                'entityCollection'  => $this->entityCollection,
                'identityContainer' => $this->invoiceIdentity,
            ]
        );
        $this->transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Sales/_files/invoice_list_different_stores.php
     * @magentoConfigFixture default/sales_email/general/async_sending 1
     * @magentoConfigFixture fixture_second_store_store sales_email/invoice/enabled 0
     * @return void
     */
    public function testInvoiceEmailSenderExecute(): void
    {
        $invoiceCollection = clone $this->entityCollection;
        $invoiceCollection->addFieldToFilter('send_email', ['eq' => 1]);
        $invoiceCollection->addFieldToFilter(InvoiceInterface::EMAIL_SENT, ['null' => true]);
        $this->emailSenderHandler->sendEmails();
        $this->assertEquals(1, $invoiceCollection->getTotalCount());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Sales/_files/invoice_with_send_email_flag.php
     * @magentoConfigFixture default/sales_email/general/async_sending 1
     * @return void
     */
    public function testSendEmailsCheckEmailReceived(): void
    {
        $invoiceCollection = clone $this->entityCollection;
        $this->emailSenderHandler->sendEmails();
        /** @var InvoiceInterface $invoice */
        $invoice = $invoiceCollection->getFirstItem();
        $this->assertNotNull($invoice->getId());
        $message = $this->transportBuilderMock->getSentMessage();
        $this->assertNotNull($message, 'The message is expected to be received');
        $subject = __('Invoice for your %1 order', $invoice->getStore()->getFrontendName())->render();
        $this->assertEquals($message->getSubject(), $subject);
        $this->assertStringContainsString(
            sprintf(
                "Your Invoice #%s for Order #%s",
                $invoice->getIncrementId(),
                $invoice->getOrder()->getIncrementId()
            ),
            quoted_printable_decode($message->getBody()->bodyToString())
        );
    }
}
