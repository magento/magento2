<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Change subscription status. Subscribe OR unsubscribe if required
 */
class ChangeSubscriptionStatus
{
    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        SubscriberFactory $subscriberFactory
    ) {
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Change subscription status. Subscribe OR unsubscribe if required
     *
     * @param int $customerId
     * @param bool $subscriptionStatus
     * @return void
     */
    public function execute(int $customerId, bool $subscriptionStatus): void
    {
        $subscriber = $this->subscriberFactory->create()->loadByCustomerId($customerId);

        if ($subscriptionStatus === true && !$subscriber->isSubscribed()) {
            $this->subscriberFactory->create()->subscribeCustomerById($customerId);
        } elseif ($subscriptionStatus === false && $subscriber->isSubscribed()) {
            $this->subscriberFactory->create()->unsubscribeCustomerById($customerId);
        }
    }
}
