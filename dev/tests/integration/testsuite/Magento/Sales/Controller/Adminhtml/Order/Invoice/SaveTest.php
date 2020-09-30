<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\Escaper;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Item;
use PHPUnit\Framework\Constraint\StringContains;

/**
 * Class tests invoice creation in admin panel.
 *
 * @see \Magento\Sales\Controller\Adminhtml\Order\Invoice\Save
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class SaveTest extends AbstractInvoiceControllerTest
{
    /** @var string  */
    protected $uri = 'backend/sales/order_invoice/save';

    /** @var Escaper */
    private $escaper;

    /** @var Item */
    private $orderItemResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->orderItemResource = $this->_objectManager->get(Item::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testSendEmailOnInvoiceSave(): void
    {
        $order = $this->getOrder('100000001');
        $itemId = $order->getItemsCollection()->getFirstItem()->getId();
        $post = $this->hydratePost([$itemId => 2]);
        $this->prepareRequest($post, ['order_id' => $order->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/save');
        $invoice = $this->getInvoiceByOrder($order);
        $this->checkSuccess($invoice, 2);
        $message = $this->transportBuilder->getSentMessage();
        $this->assertNotNull($message);
        $subject = __('Invoice for your %1 order', $order->getStore()->getFrontendName())->render();
        $messageConstraint = $this->logicalAnd(
            new StringContains($invoice->getBillingAddress()->getName()),
            new StringContains(
                'Thank you for your order from ' . $invoice->getStore()->getFrontendName()
            ),
            new StringContains(
                "Your Invoice #{$invoice->getIncrementId()} for Order #{$order->getIncrementId()}"
            )
        );
        $this->assertEquals($message->getSubject(), $subject);
        $bodyParts = $message->getBody()->getParts();
        $this->assertThat(reset($bodyParts)->getRawContent(), $messageConstraint);
    }

    /**
     * @magentoConfigFixture current_store sales_email/invoice/enabled 0
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testSendEmailOnInvoiceSaveWithDisabledConfig(): void
    {
        $order = $this->getOrder('100000001');
        $post = $this->hydratePost([$order->getItemsCollection()->getFirstItem()->getId() => 2]);
        $this->prepareRequest($post, ['order_id' => $order->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/save');
        $this->checkSuccess($this->getInvoiceByOrder($order), 2);
        $this->assertNull($this->transportBuilder->getSentMessage());
    }

    /**
     * @dataProvider invoiceDataProvider
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @param int $invoicedItemsQty
     * @param string $commentMessage
     * @param bool $doShipment
     * @return void
     */
    public function testSuccessfulInvoice(
        int $invoicedItemsQty,
        string $commentMessage = '',
        bool $doShipment = false
    ): void {
        $order = $this->getOrder('100000001');
        $post = $this->hydratePost(
            [$order->getItemsCollection()->getFirstItem()->getId() => $invoicedItemsQty],
            $commentMessage,
            $doShipment
        );
        $this->prepareRequest($post, ['order_id' => $order->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/save');
        $this->checkSuccess($this->getInvoiceByOrder($order), $invoicedItemsQty, $commentMessage, $doShipment);
    }

    /**
     * @return array
     */
    public function invoiceDataProvider(): array
    {
        return [
            'with_comment_message' => [
                'invoiced_items_qty' => 2,
                'comment_message' => 'test comment message',
            ],
            'partial_invoice' => [
                'invoiced_items_qty' => 1,
            ],
            'with_do_shipment' => [
                'invoiced_items_qty' => 2,
                'comment_message' => '',
                'do_shipment' => true,
            ],
        ];
    }

    /**
     * @return void
     */
    public function testWitNoExistingOrder(): void
    {
        $expectedMessage = (string)__('The order no longer exists.');
        $this->prepareRequest(['order_id' => 899989]);
        $this->dispatch('backend/sales/order_invoice/save');
        $this->assertErrorResponse($expectedMessage);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_bundle_and_invoiced.php
     *
     * @return void
     */
    public function testCanNotInvoiceOrder(): void
    {
        $expectedMessage = (string)__('The order does not allow an invoice to be created.');
        $order = $this->getOrder('100000001');
        $this->prepareRequest([], ['order_id' => $order->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/save');
        $this->assertErrorResponse($expectedMessage);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testInvoiceWithoutQty(): void
    {
        $expectedMessage = (string)__('The invoice can\'t be created without products. Add products and try again.');
        $order = $this->getOrder('100000001');
        $post = $this->hydratePost([$order->getItemsCollection()->getFirstItem()->getId() => '0']);
        $this->prepareRequest($post, ['order_id' => $order->getEntityId()]);
        $this->dispatch('backend/sales/order_invoice/save');
        $this->assertErrorResponse($this->escaper->escapeHtml($expectedMessage));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_configurable_product.php
     *
     * @return void
     */
    public function testPartialInvoiceWitConfigurableProduct(): void
    {
        $order = $this->getOrder('100000001');
        $post = $this->hydratePost([$order->getItemsCollection()->getFirstItem()->getId() => '1']);
        $this->prepareRequest($post, ['order_id' => $order->getEntityId()]);
        $this->dispatch($this->uri);
        $this->assertSessionMessages($this->containsEqual((string)__('The invoice has been created.')));
        $orderItems = $this->getOrderItemsQtyInvoiced((int)$order->getEntityId());
        $this->assertCount(2, $orderItems);
        $this->assertEquals(1, (int)$orderItems[0]);
        $this->assertEquals($orderItems[0], $orderItems[1]);
    }

    /**
     * Get order items qty invoiced
     *
     * @param int $orderId
     * @return array
     */
    private function getOrderItemsQtyInvoiced(int $orderId): array
    {
        $connection = $this->orderItemResource->getConnection();
        $select = $connection->select()
            ->from($this->orderItemResource->getMainTable(), OrderItemInterface::QTY_INVOICED)
            ->where(OrderItemInterface::ORDER_ID . ' = ?', $orderId);

        return $connection->fetchCol($select);
    }

    /**
     * @inheritdoc
     */
    public function testAclHasAccess()
    {
        $this->prepareRequest();

        parent::testAclHasAccess();
    }

    /**
     * @inheritdoc
     */
    public function testAclNoAccess()
    {
        $this->prepareRequest();

        parent::testAclNoAccess();
    }

    /**
     * Checks that order protect code is not changing after invoice submitting
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testOrderProtectCodePreserveAfterInvoiceSave(): void
    {
        $order = $this->getOrder('100000001');
        $this->prepareRequest([], ['order_id' => $order->getEntityId()]);
        $protectCode = $order->getProtectCode();
        $this->dispatch($this->uri);
        $invoicedOrder = $this->getOrder('100000001');

        $this->assertEquals($protectCode, $invoicedOrder->getProtectCode());
    }

    /**
     * Check error response
     *
     * @param string $expectedMessage
     * @return void
     */
    private function assertErrorResponse(string $expectedMessage): void
    {
        $this->assertRedirect($this->stringContains('sales/order_invoice/new'));
        $this->assertSessionMessages($this->containsEqual($expectedMessage));
    }

    /**
     * Check that invoice was successfully created
     *
     * @param InvoiceInterface $invoice
     * @param int $invoicedItemsQty
     * @param string|null $commentMessage
     * @param bool $doShipment
     * @return void
     */
    private function checkSuccess(
        InvoiceInterface $invoice,
        int $invoicedItemsQty,
        ?string $commentMessage = null,
        bool $doShipment = false
    ): void {
        $message = $doShipment ? 'You created the invoice and shipment.' : 'The invoice has been created.';
        $expectedState = $doShipment ? Order::STATE_COMPLETE : Order::STATE_PROCESSING;
        $this->assertNotNull($invoice->getEntityId());
        $this->assertEquals($invoicedItemsQty, (int)$invoice->getTotalQty());
        $order = $invoice->getOrder();
        $this->assertEquals($expectedState, $order->getState());

        if ($commentMessage) {
            $this->assertEquals($commentMessage, $invoice->getCustomerNote());
        }

        $this->assertRedirect(
            $this->stringContains(sprintf('sales/order/view/order_id/%u', (int)$order->getEntityId()))
        );
        $this->assertSessionMessages($this->containsEqual((string)__($message)));
    }
}
