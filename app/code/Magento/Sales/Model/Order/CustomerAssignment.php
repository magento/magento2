<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Assign customer to order.
 */
class CustomerAssignment
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * CustomerAssignment constructor.
     *
     * @param ManagerInterface $eventManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ManagerInterface $eventManager,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->eventManager = $eventManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Assign customer to order.
     *
     * @param OrderInterface $order
     * @param CustomerInterface $customer
     */
    public function execute(OrderInterface $order, CustomerInterface $customer): void
    {
        $order->setCustomerId($customer->getId())
            ->setCustomerIsGuest(false)
            ->setCustomerEmail($customer->getEmail())
            ->setCustomerFirstname($customer->getFirstname())
            ->setCustomerLastname($customer->getLastname())
            ->setCustomerMiddlename($customer->getMiddlename())
            ->setCustomerPrefix($customer->getPrefix())
            ->setCustomerSuffix($customer->getSuffix())
            ->setCustomerGroupId($customer->getGroupId());

        $this->orderRepository->save($order);

        $this->eventManager->dispatch(
            'sales_order_customer_assign_after',
            [
                'order'     => $order,
                'customer'  => $customer
            ]
        );
    }
}
