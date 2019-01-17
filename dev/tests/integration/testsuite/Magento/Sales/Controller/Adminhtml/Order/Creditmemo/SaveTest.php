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
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Provide tests for CreditMemo save controller.
 *
 * @magentoAppArea adminhtml
 */
class SaveTest extends AbstractBackendController
{
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
        $requestParams = [
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
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams($requestParams);
        $this->dispatch('backend/sales/order_creditmemo/save');

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
        $requestParams = [
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
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams($requestParams);
        $this->dispatch('backend/sales/order_creditmemo/save');

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
}
