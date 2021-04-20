<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model;

/**
 * This service provides caching Subscriber by Customer id.
 */
class CustomerSubscriberCache
{
    /**
     * @var array
     */
    private $customerSubscriber = [];

    /**
     * Get Subscriber from cache by Customer id.
     *
     * @param int $customerId
     * @return Subscriber|null
     */
    public function getCustomerSubscriber(int $customerId): ?Subscriber
    {
        $subscriber = null;
        if (isset($this->customerSubscriber[$customerId])) {
            $subscriber = $this->customerSubscriber[$customerId];
        }

        return $subscriber;
    }

    /**
     * Set Subscriber to cache by Customer id.
     *
     * @param int $customerId
     * @param Subscriber|null $subscriber
     */
    public function setCustomerSubscriber(int $customerId, ?Subscriber $subscriber): void
    {
        $this->customerSubscriber[$customerId] = $subscriber;
    }
}
