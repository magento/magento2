<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Signifyd\Api\GuaranteeCancelingServiceInterface;

/**
 * Plugin complements Order::cancel method logic with canceling Signifyd guarantee.
 *
 * @see Order::cancel
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
