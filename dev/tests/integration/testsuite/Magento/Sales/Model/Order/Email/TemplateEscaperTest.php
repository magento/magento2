<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Sales\Model\Order\Email;

use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Invoice\Sender\EmailSender as InvoiceEmailSender;
use Magento\Sales\Model\Order\Shipment\Sender\EmailSender as ShipmentEmailSender;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\StringContains;

/**
 * Test that custom file option values are not double-escaped in email templates.
 */
class TemplateEscaperTest extends TestCase
{
    /**
     * @var TransportBuilderMock
     */
    private $transportBuilder;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var InvoiceEmailSender
     */
    private $invoiceEmailSender;

    /**
     * @var ShipmentEmailSender
     */
    private $shipmentEmailSender;

    /**
     * @var CreditmemoSender
     */
    private $creditmemoSender;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->transportBuilder = $objectManager->get(TransportBuilderMock::class);
        $this->orderSender = $objectManager->get(OrderSender::class);
        $this->invoiceEmailSender = $objectManager->get(InvoiceEmailSender::class);
        $this->shipmentEmailSender = $objectManager->get(ShipmentEmailSender::class);
        $this->creditmemoSender = $objectManager->get(CreditmemoSender::class);
        $this->escaper = $objectManager->get(Escaper::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        if ($this->transportBuilder) {
            $this->transportBuilder->clean();
        }
        parent::tearDown();
    }

    /**
     * Test that custom file option values are not double-escaped in email templates.
     *
     * This test verifies the case where file upload custom option values
     * (which contain HTML links) were showing as HTML code instead of clickable links
     * in order confirmation, invoice, shipment, and creditmemo emails.
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoAppArea frontend
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws LocalizedException|\Exception
     */
    public function testCustomFileOptionValueNotDoubleEscapedInEmails(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $order = $objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        // Simulate a file custom option value that contains HTML (like a link)
        // This is what happens when a file is uploaded - the value becomes an HTML link
        $fileOptionValue = '<a href="https://example.com/download/file.jpg">file.jpg</a>';
        $fileOptionLabel = 'Sample Image';

        // Get the first order item and add custom option data
        // The options are stored in product_options['options'] array
        $orderItem = $order->getAllItems()[0];
        $productOptions = $orderItem->getProductOptions();
        if (!isset($productOptions['options'])) {
            $productOptions['options'] = [];
        }

        // Add a file custom option with HTML link as value
        // The structure matches what getItemOptions() returns
        $productOptions['options'][] = [
            'label' => $fileOptionLabel,
            'value' => $fileOptionValue,
            'print_value' => $fileOptionValue,
            'option_id' => 1,
            'option_type' => 'file',
        ];

        $orderItem->setProductOptions($productOptions);
        $orderItem->save();
        $order->save();

        // Test Order Email
        $this->transportBuilder->clean();
        $this->orderSender->send($order, true);
        $message = $this->transportBuilder->getSentMessage();
        $this->assertNotNull($message, 'Order email should be sent');
        $body = quoted_printable_decode($message->getBody()->bodyToString());

        // Verify the file option value appears as HTML link, not escaped HTML code
        $this->assertThat(
            $body,
            new StringContains($fileOptionLabel),
            'Order email should contain custom option label'
        );
        // The link should appear as HTML, not as escaped entities
        // Not expected: &lt;a href="..."&gt;file.jpg&lt;/a&gt;
        // Expected: <a href="...">file.jpg</a>
        $this->assertStringContainsString(
            '<a href=',
            $body,
            'Order email should contain HTML link tag (not escaped)'
        );
        $this->assertStringNotContainsString(
            '&lt;a href=',
            $body,
            'Order email should not contain escaped HTML link tag'
        );

        // Test Invoice Email
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        if ($invoice->getId()) {
            $this->transportBuilder->clean();
            $this->invoiceEmailSender->send($order, $invoice, null, true);
            $message = $this->transportBuilder->getSentMessage();
            $this->assertNotNull($message, 'Invoice email should be sent');
            $body = quoted_printable_decode($message->getBody()->bodyToString());

            $this->assertThat(
                $body,
                new StringContains($fileOptionLabel),
                'Invoice email should contain custom option label'
            );
            $this->assertStringContainsString(
                '<a href=',
                $body,
                'Invoice email should contain HTML link tag (not escaped)'
            );
            $this->assertStringNotContainsString(
                '&lt;a href=',
                $body,
                'Invoice email should not contain escaped HTML link tag'
            );
        }

        // Test Shipment Email
        $shipment = $order->getShipmentsCollection()->getFirstItem();
        if ($shipment->getId()) {
            $this->transportBuilder->clean();
            $this->shipmentEmailSender->send($order, $shipment, null, true);
            $message = $this->transportBuilder->getSentMessage();
            $this->assertNotNull($message, 'Shipment email should be sent');
            $body = quoted_printable_decode($message->getBody()->bodyToString());

            $this->assertThat(
                $body,
                new StringContains($fileOptionLabel),
                'Shipment email should contain custom option label'
            );
            $this->assertStringContainsString(
                '<a href=',
                $body,
                'Shipment email should contain HTML link tag (not escaped)'
            );
            $this->assertStringNotContainsString(
                '&lt;a href=',
                $body,
                'Shipment email should not contain escaped HTML link tag'
            );
        }
    }
}
