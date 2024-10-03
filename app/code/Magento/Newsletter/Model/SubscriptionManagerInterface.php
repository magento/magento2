<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model;

/**
 * Interface to update newsletter subscription status
 *
 * @api
 */
interface SubscriptionManagerInterface
{
    /**
     * Subscribe to newsletters by email
     *
     * @param string $email
     * @param int $storeId
     * @return Subscriber
     */
    public function subscribe(string $email, int $storeId): Subscriber;

    /**
     * Unsubscribe from newsletters by email
     *
     * @param string $email
     * @param int $storeId
     * @param string $confirmCode
     * @return Subscriber
     */
    public function unsubscribe(string $email, int $storeId, string $confirmCode): Subscriber;

    /**
     * Subscribe customer to newsletter
     *
     * @param int $customerId
     * @param int $storeId
     * @return Subscriber
     */
    public function subscribeCustomer(int $customerId, int $storeId): Subscriber;

    /**
     * Unsubscribe customer from newsletter
     *
     * @param int $customerId
     * @param int $storeId
     * @return Subscriber
     */
    public function unsubscribeCustomer(int $customerId, int $storeId): Subscriber;
}
