<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Sales\Api\OrderManagementInterface;

/**
 * Default implementation for OrderPlaceInterface.
 */
class PlaceOrderDefault implements PlaceOrderInterface
{
    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        OrderManagementInterface $orderManagement
    ) {
        $this->orderManagement = $orderManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function place(array $orderList): array
    {
        $errorList = [];
        foreach ($orderList as $order) {
            try {
                $this->orderManagement->place($order);
            } catch (\Exception $e) {
                $incrementId = $order->getIncrementId();
                $errorList[$incrementId] = $e;
            }
        }

        return $errorList;
    }
}
