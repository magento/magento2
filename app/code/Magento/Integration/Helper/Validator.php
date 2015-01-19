<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Helper;

use Magento\Framework\Exception\InputException;

/**
 * Validator Helper for user credentials
 */
class Validator
{
    /**
     * Validate user credentials
     *
     * @param string $username
     * @param string $password
     * @throws InputException
     * @return void
     */
    public function validateCredentials($username, $password)
    {
        $exception = new InputException();
        if (!is_string($username) || strlen($username) == 0) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'username']);
        }
        if (!is_string($username) || strlen($password) == 0) {
            $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => 'password']);
        }
        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }
}
