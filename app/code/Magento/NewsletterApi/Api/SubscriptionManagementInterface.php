<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api;

/**
 * Newsletter Subscription Management Interface fot Customers
 *
 * @api
 */
interface SubscriptionManagementInterface
{
    /**
     * Subscribe a customer
     *
     * Adds a new Newsletter Subscription for the given customer
     *
     * @param int $customerId the id of the customer to subscribe
     * @param \Magento\NewsletterApi\Api\Data\SubscriptionInterface $subscription the subscription
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException if an error occurred during subscription
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     */
    public function subscribe($customerId, Data\SubscriptionInterface $subscription);

    /**
     * Get Subscription for given Customer
     *
     * retrieves the subscription entity for the given customer id
     *
     * @param int $customerId the id of the customer
     *
     * @return \Magento\NewsletterApi\Api\Data\SubscriptionInterface $subscription
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException when subscription does not exist
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     */
    public function getSubscriptionForCustomer($customerId);

    /**
     * unsubscribe customer
     *
     * remove the subscription entity linked to the given customer
     * from the underlying persistence layer
     *
     * @param int $customerId the id of the customer to unsubscribe
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     * @throws \Magento\Framework\Exception\CouldNotDeleteException when an error occurred during unsubscribe
     * @throws \Magento\Framework\Exception\StateException when entity is in invalid state for deletion
     */
    public function unsubscribe($customerId);
}
