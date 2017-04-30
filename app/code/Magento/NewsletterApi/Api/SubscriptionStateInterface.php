<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Newsletter Subscription State Interface
 *
 * represents the subscription state.
 * Each possible state that changes behaviour of the subscription should
 * implement this interface. all state dependent methods of the subscription
 * management should be delegated to the state object
 *
 * @api
 */
interface SubscriptionStateInterface extends ExtensibleDataInterface
{
    /**
     * Subscribe
     *
     * Adds a new Newsletter Subscription
     *
     * @param \Magento\NewsletterApi\Api\Data\SubscriptionInterface $subscription the subscription
     * @param \Magento\NewsletterApi\Api\SubscriptionManagementInterface $subscriptionManagement
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException if an error occurred during subscription
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     */
    public function subscribe(
        Data\SubscriptionInterface $subscription,
        SubscriptionManagementInterface $subscriptionManagement
    );

    /**
     * unsubscribe
     *
     * unsubscribe by given email address
     *
     * @param string $email
     * @param \Magento\NewsletterApi\Api\SubscriptionManagementInterface $subscriptionManagement
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     * @throws \Magento\Framework\Exception\CouldNotDeleteException when an error occurred during unsubscribe
     * @throws \Magento\Framework\Exception\StateException when entity is in invalid state for deletion
     */
    public function unsubscribe($email, SubscriptionManagementInterface $subscriptionManagement);

    /**
     * Subscribe a customer
     *
     * Adds a new Newsletter Subscription for the given customer
     *
     * @param int $customerId the id of the customer to subscribe
     * @param \Magento\NewsletterApi\Api\Data\SubscriptionInterface $subscription the subscription
     * @param \Magento\NewsletterApi\Api\SubscriptionManagementInterface $subscriptionManagement
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException if an error occurred during subscription
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     */
    public function subscribeCustomer(
        $customerId,
        Data\SubscriptionInterface $subscription,
        SubscriptionManagementInterface $subscriptionManagement
    );

    /**
     * unsubscribe customer
     *
     * remove the subscription entity linked to the given customer
     * from the underlying persistence layer
     *
     * @param int $customerId the id of the customer to unsubscribe
     * @param \Magento\NewsletterApi\Api\SubscriptionManagementInterface $subscriptionManagement
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     * @throws \Magento\Framework\Exception\CouldNotDeleteException when an error occurred during unsubscribe
     * @throws \Magento\Framework\Exception\StateException when entity is in invalid state for deletion
     */
    public function unsubscribeCustomer($customerId, SubscriptionManagementInterface $subscriptionManagement);
}
