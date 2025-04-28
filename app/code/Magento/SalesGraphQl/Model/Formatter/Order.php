<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Formatter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Order\OrderAddress;
use Magento\SalesGraphQl\Model\Order\OrderPayments;

/**
 * Format order model for graphql schema
 */
class Order
{
    /**
     * @param OrderAddress $orderAddress
     * @param OrderPayments $orderPayments
     * @param TimezoneInterface|null $timezone
     */
    public function __construct(
        private readonly OrderAddress $orderAddress,
        private readonly OrderPayments $orderPayments,
        private ?TimezoneInterface $timezone = null
    ) {
        $this->timezone = $timezone ?: ObjectManager::getInstance()->get(TimezoneInterface::class);
    }

    /**
     * Format order model for graphql schema
     *
     * @param OrderInterface $orderModel
     * @return array
     * @throws LocalizedException
     */
    public function format(OrderInterface $orderModel): array
    {
        return [
            'created_at' => $orderModel->getCreatedAt(),
            'grand_total' => $orderModel->getGrandTotal(),
            'id' => base64_encode((string)$orderModel->getEntityId()),
            'increment_id' => $orderModel->getIncrementId(),
            'number' => $orderModel->getIncrementId(),
            'order_date' => $this->timezone->date($orderModel->getCreatedAt())
                ->format(DateTime::DATETIME_PHP_FORMAT),
            'order_number' => $orderModel->getIncrementId(),
            'status' => $orderModel->getStatusLabel(),
            'email' => $orderModel->getCustomerEmail(),
            'shipping_method' => $orderModel->getShippingDescription(),
            'shipping_address' => $this->orderAddress->getOrderShippingAddress($orderModel),
            'billing_address' => $this->orderAddress->getOrderBillingAddress($orderModel),
            'payment_methods' => $this->orderPayments->getOrderPaymentMethod($orderModel),
            'applied_coupons' => $orderModel->getCouponCode() ? ['code' => $orderModel->getCouponCode()] : [],
            'model' => $orderModel,
            'comments' => $this->getOrderComments($orderModel)
        ];
    }

    /**
     * Get order comments
     *
     * @param OrderInterface $order
     * @return array
     */
    public function getOrderComments(OrderInterface $order):array
    {
        $comments = [];
        foreach ($order->getStatusHistories() as $comment) {
            if ($comment->getIsVisibleOnFront()) {
                $comments[] = [
                    'message' => $comment->getComment(),
                    'timestamp' => $comment->getCreatedAt()
                ];
            }
        }
        return $comments;
    }
}
