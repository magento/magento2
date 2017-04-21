<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewsletterApi\Api;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Newsletter Subscription Management Interface
 *
 * @api
 */
interface SubscriptionManagementInterface
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
     * @throws \Magento\NewsletterApi\Exception\AlreadySubscribedException when subscription already exists
     * @throws \Magento\NewsletterApi\Exception\CouldNotSubscribeException if an error occurred during subscription
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     */
    public function subscribe(Data\SubscriptionInterface $subscription);

    /**
     * Subscribe a customer
     *
     * Adds a new Newsletter Subscription for the given customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer the customer to be subscribed
     *
     * @return bool true on success
     *
     * @throws \Magento\NewsletterApi\Exception\AlreadySubscribedException when subscription already exists
     * @throws \Magento\NewsletterApi\Exception\CouldNotSubscribeException if an error occurred during subscription
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     */
    public function subscribeCustomer(CustomerInterface $customer);

    /**
     * Subscribe a customer by id
     *
     * Adds a new Newsletter Subscription for the given customer
     * by provided customer id
     *
     * @param int $customerId the id of the customer to be subscribed
     *
     * @return bool true on success
     *
     * @throws \Magento\NewsletterApi\Exception\AlreadySubscribedException when subscription already exists
     * @throws \Magento\NewsletterApi\Exception\CouldNotSubscribeException if an error occurred during subscription
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     */
    public function subscribeCustomerById(int $customerId);

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
     * @throws \Magento\NewsletterApi\Exception\CouldNotUnSubscribeException when an error occurred during unsubscribe
     */
    public function unsubscribe(string $email);

    /**
     * unsubscribe customer
     *
     * remove the subscription entity linked to the given customer
     * from the underlying persistence layer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer the customer to unsubscribe
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     * @throws \Magento\NewsletterApi\Exception\CouldNotUnSubscribeException when an error occurred during unsubscribe
     */
    public function unsubscribeCustomer(CustomerInterface $customer);

    /**
     * unsubscribe customer by id
     *
     * remove the subscription entity linked to the given customer id
     * from the underlying persistence layer
     *
     * @param int $customerId the id of the customer to unsubscribe
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\InputException when an invalid input has been provided
     * @throws \Magento\NewsletterApi\Exception\CouldNotUnSubscribeException when an error occurred during unsubscribe
     */
    public function unsubscribeCustomerById(int $customerId);
}
