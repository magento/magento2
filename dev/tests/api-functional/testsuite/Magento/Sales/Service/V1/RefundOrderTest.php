<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\Sales\Model\Order;

/**
 * API test for creation of Creditmemo for certain Order.
 */
class RefundOrderTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_READ_NAME = 'salesRefundOrderV1';
    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->creditmemoRepository = $this->objectManager->get(
            \Magento\Sales\Api\CreditmemoRepositoryInterface::class
        );
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_with_shipping_and_invoice.php
     */
    public function testShortRequest()
    {
        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        $result = $this->_webApiCall(
            $this->getServiceData($existingOrder),
            ['orderId' => $existingOrder->getEntityId()]
        );

        $this->assertNotEmpty(
            $result,
            'Failed asserting that the received response is correct'
        );

        /** @var \Magento\Sales\Model\Order $updatedOrder */
        $updatedOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId($existingOrder->getIncrementId());

        try {
            $creditmemo = $this->creditmemoRepository->get($result);

            $expectedItems = $this->getOrderItems($existingOrder);
            $actualCreditmemoItems = $this->getCreditmemoItems($creditmemo);
            $actualRefundedOrderItems = $this->getRefundedOrderItems($updatedOrder);

            $this->assertEquals(
                $expectedItems,
                $actualCreditmemoItems,
                'Failed asserting that the Creditmemo contains all requested items'
            );

            $this->assertEquals(
                $expectedItems,
                $actualRefundedOrderItems,
                'Failed asserting that all requested order items were refunded'
            );

            $this->assertEquals(
                $creditmemo->getShippingAmount(),
                $existingOrder->getShippingAmount(),
                'Failed asserting that the Creditmemo contains correct shipping amount'
            );

            $this->assertEquals(
                $creditmemo->getShippingAmount(),
                $updatedOrder->getShippingRefunded(),
                'Failed asserting that proper shipping amount of the Order was refunded'
            );

            $this->assertEquals(
                Order::STATE_COMPLETE,
                $updatedOrder->getStatus(),
                'Failed asserting that order status has not changed'
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->fail('Failed asserting that Creditmemo was created');
        }
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_with_shipping_and_invoice.php
     */
    public function testFullRequest()
    {
        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        $expectedItems = $this->getOrderItems($existingOrder);
        $expectedItems[0]['qty'] = $expectedItems[0]['qty'] - 1;

        $expectedComment = [
            'comment' => 'Test Comment',
            'is_visible_on_front' => 1
        ];

        $expectedShippingAmount = 15;
        $expectedAdjustmentPositive = 5.53;
        $expectedAdjustmentNegative = 5.53;

        $result = $this->_webApiCall(
            $this->getServiceData($existingOrder),
            [
                'orderId' => $existingOrder->getEntityId(),
                'items' => $expectedItems,
                'comment' => $expectedComment,
                'arguments' => [
                    'shipping_amount' => $expectedShippingAmount,
                    'adjustment_positive' => $expectedAdjustmentPositive,
                    'adjustment_negative' => $expectedAdjustmentNegative
                ]
            ]
        );

        $this->assertNotEmpty(
            $result,
            'Failed asserting that the received response is correct'
        );

        /** @var \Magento\Sales\Model\Order $updatedOrder */
        $updatedOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId($existingOrder->getIncrementId());

        try {
            $creditmemo = $this->creditmemoRepository->get($result);

            $actualCreditmemoItems = $this->getCreditmemoItems($creditmemo);
            $actualCreditmemoComment = $this->getRecentComment($creditmemo);
            $actualRefundedOrderItems = $this->getRefundedOrderItems($updatedOrder);

            $this->assertEquals(
                $expectedItems,
                $actualCreditmemoItems,
                'Failed asserting that the Creditmemo contains all requested items'
            );

            $this->assertEquals(
                $expectedItems,
                $actualRefundedOrderItems,
                'Failed asserting that all requested order items were refunded'
            );

            $this->assertEquals(
                $expectedComment,
                $actualCreditmemoComment,
                'Failed asserting that the Creditmemo contains correct comment'
            );

            $this->assertEquals(
                $expectedShippingAmount,
                $creditmemo->getShippingAmount(),
                'Failed asserting that the Creditmemo contains correct shipping amount'
            );

            $this->assertEquals(
                $expectedShippingAmount,
                $updatedOrder->getShippingRefunded(),
                'Failed asserting that proper shipping amount of the Order was refunded'
            );

            $this->assertEquals(
                $expectedAdjustmentPositive,
                $creditmemo->getAdjustmentPositive(),
                'Failed asserting that the Creditmemo contains correct positive adjustment'
            );

            $this->assertEquals(
                $expectedAdjustmentNegative,
                $creditmemo->getAdjustmentNegative(),
                'Failed asserting that the Creditmemo contains correct negative adjustment'
            );

            $this->assertEquals(
                $existingOrder->getStatus(),
                $updatedOrder->getStatus(),
                'Failed asserting that order status was NOT changed'
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->fail('Failed asserting that Creditmemo was created');
        }
    }

    /**
     * Prepares and returns info for API service.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return array
     */
    private function getServiceData(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return [
            'rest' => [
                'resourcePath' => '/V1/order/' . $order->getEntityId() . '/refund',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'execute',
            ]
        ];
    }

    /**
     * Gets all items of given Order in proper format.
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return array
     */
    private function getOrderItems(\Magento\Sales\Model\Order $order)
    {
        $items = [];

        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($order->getAllItems() as $item) {
            $items[] = [
                'order_item_id' => $item->getItemId(),
                'qty' => $item->getQtyOrdered(),
            ];
        }

        return $items;
    }

    /**
     * Gets refunded items of given Order in proper format.
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return array
     */
    private function getRefundedOrderItems(\Magento\Sales\Model\Order $order)
    {
        $items = [];

        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyRefunded() > 0) {
                $items[] = [
                    'order_item_id' => $item->getItemId(),
                    'qty' => $item->getQtyRefunded(),
                ];
            }
        }

        return $items;
    }

    /**
     * Gets all items of given Creditmemo in proper format.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     *
     * @return array
     */
    private function getCreditmemoItems(\Magento\Sales\Api\Data\CreditmemoInterface $creditmemo)
    {
        $items = [];

        /** @var \Magento\Sales\Api\Data\CreditmemoItemInterface $item */
        foreach ($creditmemo->getItems() as $item) {
            $items[] = [
                'order_item_id' => $item->getOrderItemId(),
                'qty' => $item->getQty(),
            ];
        }

        return $items;
    }

    /**
     * Gets the most recent comment of given Creditmemo in proper format.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     *
     * @return array|null
     */
    private function getRecentComment(\Magento\Sales\Api\Data\CreditmemoInterface $creditmemo)
    {
        $comments = $creditmemo->getComments();

        if ($comments) {
            $comment = reset($comments);

            return [
                'comment' => $comment->getComment(),
                'is_visible_on_front' => $comment->getIsVisibleOnFront(),
            ];
        }

        return null;
    }
}
