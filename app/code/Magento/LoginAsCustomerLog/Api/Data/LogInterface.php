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
 */
interface LogInterface extends ExtensibleDataInterface
{
    const LOG_ID = 'log_id';
    const TIME = 'time';
    const ACTION_GROUP = 'action_group';
    const ACTION = 'action';
    const FULL_ACTION_NAME = 'full_action_name';
    const RESULT = 'result';
    const DETAILS = 'details';
    const ERROR = 'error';
    const IP_ADDRESS = 'ip_address';
    const USERNAME = 'username';
    const CUSTOMER_ID = 'customer_id';

    /**
     * Set login as customer log id.
     *
     * @param int $logId
     * @return void
     */
    public function setLogId(int $logId): void;

    /**
     * Retrieve login as customer log id.
     *
     * @return null|int
     */
    public function getLogId(): ?int;

    /**
     * Set login as customer log time.
     *
     * @param string $name
     * @return void
     */
    public function setTime(string $name): void;

    /**
     * Retrieve login as customer log time.
     *
     * @return null|string
     */
    public function getTime(): ?string;

    /**
     * Set login as customer log action group.
     *
     * @param string $actionGroup
     * @return void
     */
    public function setActionGroup(string $actionGroup): void;

    /**
     * Retrieve login as customer log action group.
     *
     * @return null|string
     */
    public function getActionGroup(): ?string;

    /**
     * Set login as customer log action.
     *
     * @param string $action
     * @return void
     */
    public function setAction(string $action): void;

    /**
     * Retrieve login as customer log action.
     *
     * @return null|string
     */
    public function getAction(): ?string;

    /**
     * Set login as customer log full action name.
     *
     * @param string $fullActionName
     * @return void
     */
    public function setFullActionName(string $fullActionName): void;

    /**
     * Retrieve login as customer log full action name.
     *
     * @return null|string
     */
    public function getFullActionName(): ?string;

    /**
     * Set login as customer log result.
     *
     * @param int $result
     * @return void
     */
    public function setResult(int $result): void;

    /**
     * Retrieve login as customer log result.
     *
     * @return null|int
     */
    public function getResult(): ?int;

    /**
     * Set login as customer log details.
     *
     * @param string $details
     * @return void
     */
    public function setDetails(string $details): void;

    /**
     * Retrieve login as customer log details.
     *
     * @return null|string
     */
    public function getDetails(): ?string;

    /**
     * Set login as customer log error.
     *
     * @param string $error
     * @return void
     */
    public function setError(string $error): void;

    /**
     * Retrieve login as customer log error.
     *
     * @return string|null
     */
    public function getError(): ?string;

    /**
     * Set login as customer log ip address.
     *
     * @param int $ipAddress
     * @return void
     */
    public function setIpAddress(int $ipAddress): void;

    /**
     * Retrieve login as customer log ip address.
     *
     * @return null|int
     */
    public function getIpAddress(): ?int;

    /**
     * Set login as customer log user name.
     *
     * @param string $username
     * @return void
     */
    public function setUsername(string $username): void;

    /**
     * Retrieve login as customer log user name.
     *
     * @return null|string
     */
    public function getUsername(): ?string;

    /**
     * Set login as customer log customer id.
     *
     * @param int $customerId
     * @return void
     */
    public function setCustomerId(int $customerId): void;

    /**
     * Retrieve login as customer log customer id.
     *
     * @return null|int
     */
    public function getCustomerId(): ?int;

    /**
     * Set log extension attributes.
     *
     * @param \Magento\LoginAsCustomerLog\Api\Data\LogExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(LogExtensionInterface $extensionAttributes): void;

    /**
     * Retrieve log extension attributes.
     *
     * @return \Magento\LoginAsCustomerLog\Api\Data\LogExtensionInterface
     */
    public function getExtensionAttributes(): LogExtensionInterface;
}
