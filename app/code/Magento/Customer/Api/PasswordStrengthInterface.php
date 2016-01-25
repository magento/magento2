<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

use Magento\Framework\Exception\InputException;

/**
 * Interface for password strength validation.
 */
interface PasswordStrengthInterface
{
    /**
     * Make sure that password complies with minimum security requirements.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    public function checkPasswordStrength($password);

    /**
     * Make sure that login password complies with minimum security requirements.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    public function checkLoginPasswordStrength($password);
}
