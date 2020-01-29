<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Service;

use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\PlaceOrderInterface;

/**
 * @inheritDoc
 */
class PlaceOrderService implements PlaceOrderInterface
{
    /**
     * @var OrderService
     */
    private $orderManagement;

    /**
     * PlaceOrderService constructor.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderManagement = $orderService;
    }

    /**
     * Calls the \Magento\Sales\Model\Service\OrderService::place() method to place an order
     *
     * @param OrderInterface $entity
     * @return OrderInterface
     * @throws Exception
     */
    public function save(OrderInterface $entity): OrderInterface
    {
        return $this->orderManagement->place($entity);
    }
}
