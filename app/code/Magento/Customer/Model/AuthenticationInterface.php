<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Interface \Magento\Customer\Model\AuthenticationInterface
 *
 * @since 2.1.0
 */
interface AuthenticationInterface
{
    /**
     * Process customer authentication failure
     *
     * @param int $customerId
     * @return void
     * @since 2.1.0
     */
    public function processAuthenticationFailure($customerId);

    /**
     * Unlock customer
     *
     * @param int $customerId
     * @return void
     * @since 2.1.0
     */
    public function unlock($customerId);

    /**
     * Check if a customer is locked
     *
     * @param int $customerId
     * @return boolean
     * @since 2.1.0
     */
    public function isLocked($customerId);

    /**
     * Authenticate customer
     *
     * @param int $customerId
     * @param string $password
     * @return boolean
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     * @since 2.1.0
     */
    public function authenticate($customerId, $password);
}
