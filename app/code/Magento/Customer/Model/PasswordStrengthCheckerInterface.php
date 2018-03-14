<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Model\PasswordStrengthChecker\ResultInterface;

/**
 * Interface PasswordStrengthCheckerInterface
 * @package Magento\Customer\Model
 */
interface PasswordStrengthCheckerInterface
{
    const MAX_PASSWORD_LENGTH = 256;

    /**
     * Check password strength.
     *
     * @param string $password
     * @return ResultInterface
     */
    public function check(string $password): ResultInterface;
}
