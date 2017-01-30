<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Framework\Exception\InputException;

/**
 * Validator Helper for user credentials
 */
class CredentialsValidator
{
    /**
     * Validate user credentials
     *
     * @param string $username
     * @param string $password
     * @throws InputException
     * @return void
     */
    public function validate($username, $password)
    {
        $exception = new InputException();
        if (!is_string($username) || strlen($username) == 0) {
            $exception->addError(__(InputException::REQUIRED_FIELD, ['fieldName' => 'username']));
        }
        if (!is_string($password) || strlen($password) == 0) {
            $exception->addError(__(InputException::REQUIRED_FIELD, ['fieldName' => 'password']));
        }
        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }
}
