<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api;

/**
 * Newsletter Subscription Management Interface for Guests
 *
 * @api
 */
interface GuestSubscriptionManagementInterface
{
    /**
     * Subscribe
     *
     * Adds a new Newsletter Subscription
     *
     * @param \Magento\NewsletterApi\Api\Data\SubscriptionInterface $subscription the subscription
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException when subscription already exists
     * @throws \Magento\Framework\Exception\CouldNotSaveException if an error occurred during subscription
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     */
    public function subscribe(Data\SubscriptionInterface $subscription);

    /**
     * unsubscribe
     *
     * unsubscribe by given email address
     *
     * @param string $email
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     * @throws \Magento\Framework\Exception\CouldNotDeleteException when an error occurred during unsubscribe
     * @throws \Magento\Framework\Exception\StateException when entity is in invalid state for deletion
     */
    public function unsubscribe($email);
}
