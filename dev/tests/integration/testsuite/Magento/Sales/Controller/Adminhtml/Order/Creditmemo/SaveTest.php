<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\StringContains;

/**
 * Provide tests for CreditMemo save controller.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixture Magento/Sales/_files/invoice.php
 */
class SaveTest extends AbstractCreditmemoControllerTest
{
    /**
     * @var string
     */
    protected $resource = 'Magento_Sales::creditmemo';

    /**
     * @var string
     */
    protected $uri = 'backend/sales/order_creditmemo/save';

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @return void
     */
    public function testSendEmailOnCreditmemoSave(): void
    {
        $order = $this->prepareRequest(['creditmemo' => ['send_email' => true]]);
        $this->dispatch($this->uri);

        $this->assertSessionMessages(
            $this->equalTo([(string)__('You created the credit memo.')]),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('sales/order/view/order_id/' . $order->getEntityId()));

        $creditMemo = $this->getCreditMemo($order);
        $message = $this->transportBuilder->getSentMessage();
        $subject = __('Credit memo for your %1 order', $order->getStore()->getFrontendName())->render();
        $messageConstraint = $this->logicalAnd(
            new StringContains($order->getBillingAddress()->getName()),
            new StringContains(
                'Thank you for your order from ' . $creditMemo->getStore()->getFrontendName()
            ),
            new StringContains(
                "Your Credit Memo #{$creditMemo->getIncrementId()} for Order #{$order->getIncrementId()}"
            )
        );

        $this->assertEquals($message->getSubject(), $subject);
        $this->assertThat(quoted_printable_decode($message->getBody()->bodyToString()), $messageConstraint);
    }

    /**
     * Test order will keep same(custom) status after partial refund, if state has not been changed.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_invoice_and_custom_status.php
     */
    public function testOrderStatusPartialRefund()
    {
        /** @var Order $existingOrder */
        $existingOrder = $this->_objectManager->create(Order::class)->loadByIncrementId('100000001');
        $items = $this->getOrderItems($existingOrder, 1);
        $postParams = [
            'creditmemo' => [
                'items' => $items,
                'do_offline' => '1',
                'comment_text' => '',
                'shipping_amount' => '0',
                'adjustment_positive' => '0',
                'adjustment_negative' => '0',
            ],
            'order_id' => $existingOrder->getId(),
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST)
            ->setPostValue($postParams);
        $this->dispatch($this->uri);

        /** @var Order $updatedOrder */
        $updatedOrder = $this->_objectManager->create(Order::class)
            ->loadByIncrementId($existingOrder->getIncrementId());

        $this->assertSame('custom_processing', $updatedOrder->getStatus());
        $this->assertSame('processing', $updatedOrder->getState());
    }

    /**
     * Test order will change custom status after total refund, when state has been changed.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_invoice_and_custom_status.php
     */
    public function testOrderStatusTotalRefund()
    {
        /** @var Order $existingOrder */
        $existingOrder = $this->_objectManager->create(Order::class)->loadByIncrementId('100000001');
        $postParams = [
            'creditmemo' => [
                'items' => $this->getOrderItems($existingOrder),
                'do_offline' => '1',
                'comment_text' => '',
                'shipping_amount' => '0',
                'adjustment_positive' => '0',
                'adjustment_negative' => '0',
            ],
            'order_id' => $existingOrder->getId(),
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST)
            ->setPostValue($postParams);
        $this->dispatch($this->uri);

        /** @var Order $updatedOrder */
        $updatedOrder = $this->_objectManager->create(Order::class)
            ->loadByIncrementId($existingOrder->getIncrementId());

        $this->assertSame('complete', $updatedOrder->getStatus());
        $this->assertSame('complete', $updatedOrder->getState());
    }

    /**
     * A refund amount below the adjustment fee must not create a credit memo when zero totals are allowed.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoConfigFixture current_store sales/zerograndtotal_creditmemo/allow_zero_grandtotal 1
     * @param string $doOffline
     * @return void
     */
    #[DataProvider('refundActionDataProvider')]
    public function testNegativeTotalIsRejectedWhenZeroTotalIsAllowed(string $doOffline): void
    {
        $this->assertNegativeTotalIsRejected($doOffline);
    }

    /**
     * A refund amount below the adjustment fee must not create a credit memo when zero totals are not allowed.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoConfigFixture current_store sales/zerograndtotal_creditmemo/allow_zero_grandtotal 0
     * @param string $doOffline
     * @return void
     */
    #[DataProvider('refundActionDataProvider')]
    public function testNegativeTotalIsRejectedWhenZeroTotalIsNotAllowed(string $doOffline): void
    {
        $this->assertNegativeTotalIsRejected($doOffline);
    }

    /**
     * A zero total offline credit memo is created when zero totals are allowed.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     * @magentoConfigFixture current_store sales/zerograndtotal_creditmemo/allow_zero_grandtotal 1
     * @return void
     */
    public function testZeroTotalIsCreatedWhenZeroTotalIsAllowed(): void
    {
        $order = $this->prepareAdjustmentRequest('1', '10', '10');
        $this->dispatch($this->uri);

        $this->assertSessionMessages(
            $this->equalTo([(string)__('You created the credit memo.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $creditMemo = $this->getCreditMemo($order);
        $this->assertNotNull($creditMemo->getEntityId());
        $this->assertEquals(0, $creditMemo->getGrandTotal());
        $this->assertEquals(10, $creditMemo->getAdjustmentNegative());
    }

    /**
     * Returns the validation error as the session stores it (HTML-escaped).
     *
     * @return string
     */
    private function getPositiveTotalMessage(): string
    {
        return $this->_objectManager->get(Escaper::class)
            ->escapeHtml((string)__('The credit memo\'s total must be positive.'));
    }

    /**
     * @return array
     */
    public static function refundActionDataProvider(): array
    {
        return [
            'refund' => ['0'],
            'refund offline' => ['1'],
        ];
    }

    /**
     * Posts a $10 refund with a $20 adjustment fee and checks that nothing was refunded.
     *
     * @param string $doOffline
     * @return void
     */
    private function assertNegativeTotalIsRejected(string $doOffline): void
    {
        $order = $this->prepareAdjustmentRequest($doOffline, '10', '20');
        $this->dispatch($this->uri);

        $this->assertSessionMessages(
            $this->equalTo([$this->getPositiveTotalMessage()]),
            MessageInterface::TYPE_ERROR
        );
        $this->assertNull($this->getCreditMemo($order)->getEntityId());
        $updatedOrder = $this->getOrder($order->getIncrementId());
        $this->assertEquals($order->getTotalRefunded(), $updatedOrder->getTotalRefunded());
        $this->assertEquals($order->getBaseTotalRefunded(), $updatedOrder->getBaseTotalRefunded());
        $this->assertEquals($order->getTotalOfflineRefunded(), $updatedOrder->getTotalOfflineRefunded());
        $this->assertEquals($order->getTotalOnlineRefunded(), $updatedOrder->getTotalOnlineRefunded());
    }

    /**
     * Prepares a credit memo request that refunds no items or shipping, only the given adjustments.
     *
     * @param string $doOffline
     * @param string $adjustmentRefund
     * @param string $adjustmentFee
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    private function prepareAdjustmentRequest(string $doOffline, string $adjustmentRefund, string $adjustmentFee)
    {
        $order = $this->getOrder('100000001');
        // Adjustments are converted to the order currency with this rate; the fixture order does not set it.
        $order->setBaseToOrderRate(1);
        $this->_objectManager->get(OrderRepositoryInterface::class)->save($order);
        $items = [];
        foreach ($order->getAllItems() as $item) {
            $items[$item->getItemId()] = ['qty' => 0];
        }

        return $this->prepareRequest(
            [
                'creditmemo' => [
                    'items' => $items,
                    'do_offline' => $doOffline,
                    'shipping_amount' => '0',
                    'adjustment_positive' => $adjustmentRefund,
                    'adjustment_negative' => $adjustmentFee,
                ],
            ]
        );
    }

    /**
     * Gets all items of given Order in proper format.
     *
     * @param Order $order
     * @param int $subQty
     * @return array
     */
    private function getOrderItems(Order $order, int $subQty = 0)
    {
        $items = [];
        /** @var OrderItemInterface $item */
        foreach ($order->getAllItems() as $item) {
            $items[$item->getItemId()] = [
                'qty' => $item->getQtyOrdered() - $subQty,
            ];
        }

        return $items;
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
     * @param array $params
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    private function prepareRequest(array $params = [])
    {
        $order = $this->getOrder('100000001');
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setParams(
            [
                'order_id' => $order->getEntityId(),
                'form_key' => $this->formKey->getFormKey(),
            ]
        );

        $data = ['creditmemo' => ['do_offline' => true]];
        $data = array_replace_recursive($data, $params);

        $this->getRequest()->setPostValue($data);

        return $order;
    }
}
