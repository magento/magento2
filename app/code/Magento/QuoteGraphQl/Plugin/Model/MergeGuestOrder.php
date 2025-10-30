<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin\Model;

use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order\CustomerAssignment;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class MergeGuestOrder
{
    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreManagerInterface $storeManager
     * @param CustomerAssignment $customerAssignment
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly CustomerAssignment $customerAssignment,
        private readonly CustomerRepositoryInterface $customerRepository
    ) {
    }

    /**
     * Merge guest order in  customer after place order
     *
     * @param QuoteManagement $subject
     * @param int $orderId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterPlaceOrder(QuoteManagement $subject, int $orderId)
    {
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order->getCustomerIsGuest() && $order->getCustomerEmail()) {
                try {
                    $websiteID = $this->storeManager->getStore()->getWebsiteId();
                    $customer = $this->customerRepository->get($order->getCustomerEmail(), $websiteID);
                    if ($customer->getId()) {
                        $this->customerAssignment->execute($order, $customer);
                    }
                    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
                } catch (NoSuchEntityException $e) {
                    // Do not remove this handle as it used to check that customer
                    // with this email not registered in the system
                }
            }
        }
        return $orderId;
    }
}
