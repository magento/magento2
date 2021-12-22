<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Data interface for login as customer log.
 *
 * @api
 * @since 100.4.0
 */
interface LogInterface extends ExtensibleDataInterface
{
    const LOG_ID = 'log_id';
    const TIME = 'time';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_EMAIL = 'customer_email';
    const USER_ID = 'user_id';
    const USERNAME = 'user_name';

    /**
     * Set login as customer log id.
     *
     * @param int $logId
     * @return void
     * @since 100.4.0
     */
    public function setLogId(int $logId): void;

    /**
     * Retrieve login as customer log id.
     *
     * @return null|int
     * @since 100.4.0
     */
    public function getLogId(): ?int;

    /**
     * Set login as customer log time.
     *
     * @param string $time
     * @return void
     * @since 100.4.0
     */
    public function setTime(string $time): void;

    /**
     * Retrieve login as customer log time.
     *
     * @return null|string
     * @since 100.4.0
     */
    public function getTime(): ?string;

    /**
     * Set login as customer log user id.
     *
     * @param int $userId
     * @return void
     * @since 100.4.0
     */
    public function setUserId(int $userId): void;

    /**
     * Retrieve login as customer log user id.
     *
     * @return null|int
     * @since 100.4.0
     */
    public function getUserId(): ?int;

    /**
     * Set login as customer log user name.
     *
     * @param string $userName
     * @return void
     * @since 100.4.0
     */
    public function setUserName(string $userName): void;

    /**
     * Retrieve login as customer log user name.
     *
     * @return null|string
     * @since 100.4.0
     */
    public function getUserName(): ?string;

    /**
     * Set login as customer log customer id.
     *
     * @param int $customerId
     * @return void
     * @since 100.4.0
     */
    public function setCustomerId(int $customerId): void;

    /**
     * Retrieve login as customer log customer id.
     *
     * @return null|int
     * @since 100.4.0
     */
    public function getCustomerId(): ?int;

    /**
     * Set login as customer log customer email.
     *
     * @param string $customerEmail
     * @return void
     * @since 100.4.0
     */
    public function setCustomerEmail(string $customerEmail): void;

    /**
     * Retrieve login as customer log customer email.
     *
     * @return null|string
     * @since 100.4.0
     */
    public function getCustomerEmail(): ?string;

    /**
     * Set log extension attributes.
     *
     * @param \Magento\LoginAsCustomerLog\Api\Data\LogExtensionInterface $extensionAttributes
     * @return void
     * @since 100.4.0
     */
    public function setExtensionAttributes(LogExtensionInterface $extensionAttributes): void;

    /**
     * Retrieve log extension attributes.
     *
     * @return \Magento\LoginAsCustomerLog\Api\Data\LogExtensionInterface
     * @since 100.4.0
     */
    public function getExtensionAttributes(): LogExtensionInterface;
}
