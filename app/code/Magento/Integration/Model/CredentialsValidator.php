<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
            $exception->addError(__('"%fieldName" is required. Enter and try again.', ['fieldName' => 'username']));
        }
        if (!is_string($password) || strlen($password) == 0) {
            $exception->addError(__('"%fieldName" is required. Enter and try again.', ['fieldName' => 'password']));
        }
        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }
}
