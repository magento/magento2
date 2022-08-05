<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
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
        $this->assertThat($message->getBody()->getParts()[0]->getRawContent(), $messageConstraint);
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
