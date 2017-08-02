<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Framework\Exception\InputException;

/**
 * Validator Helper for user credentials
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function validate($username, $password)
    {
        $exception = new InputException();
        if (!is_string($username) || strlen($username) == 0) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'username']));
        }
        if (!is_string($password) || strlen($password) == 0) {
            $exception->addError(__('%fieldName is a required field.', ['fieldName' => 'password']));
        }
        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }
}
