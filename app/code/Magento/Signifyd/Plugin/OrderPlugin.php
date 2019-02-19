<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Signifyd\Api\GuaranteeCancelingServiceInterface;

/**
 * Plugin for Magento\Sales\Model\Order.
 *
 * @see Order
 */
class OrderPlugin
{
    /**
     * @var GuaranteeCancelingServiceInterface
     */
    private $guaranteeCancelingService;

    /**
     * @param GuaranteeCancelingServiceInterface $guaranteeCancelingService
     */
    public function __construct(
        GuaranteeCancelingServiceInterface $guaranteeCancelingService
    ) {
        $this->guaranteeCancelingService = $guaranteeCancelingService;
    }

    /**
     * Performs Signifyd guarantee cancel operation after order canceling
     * if cancel order operation was successful.
     *
     * @see Order::cancel
     * @param Order $order
     * @param OrderInterface $result
     * @return OrderInterface
     */
    public function afterCancel(Order $order, $result)
    {
        if ($order->isCanceled()) {
            $this->guaranteeCancelingService->cancelForOrder(
                $order->getEntityId()
            );
        }

        return $result;
    }
}
