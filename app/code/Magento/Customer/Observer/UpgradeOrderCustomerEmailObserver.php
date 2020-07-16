<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Customer\Model\Data\Customer;

/**
 * Class observer UpgradeOrderCustomerEmailObserver
 * Update orders customer email after corresponding customer email changed
 */
class UpgradeOrderCustomerEmailObserver implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Upgrade order customer email when customer has changed email
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Customer $originalCustomer */
        $originalCustomer = $observer->getEvent()->getOrigCustomerDataObject();
        if (!$originalCustomer) {
            return;
        }

        /** @var Customer $customer */
        $customer = $observer->getEvent()->getCustomerDataObject();
        $customerEmail = $customer->getEmail();

        if ($customerEmail === $originalCustomer->getEmail()) {
            return;
        }
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::CUSTOMER_ID, $customer->getId())
            ->create();

        /**
         * @var Collection $orders
         */
        $orders = $this->orderRepository->getList($searchCriteria);
        $orders->setDataToAll(OrderInterface::CUSTOMER_EMAIL, $customerEmail);
        $orders->save();
    }
}
