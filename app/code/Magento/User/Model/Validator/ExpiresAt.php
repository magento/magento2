<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;

/**
 * Class ExpiresAt
 * @package Magento\User\Model\Validator
 */
class ExpiresAt extends AbstractValidator
{

    /**
     * Ensure that the given date is later than the current date.
     * @param String $value
     * @return bool
     * @throws \Exception
     */
    public function isValid($value)
    {
        $currentTime = new \DateTime();
        $expiresAt = new \DateTime($value);

        if ($expiresAt < $currentTime) {
            $message = __('The expiration date must be later than the current date.');
            $this->_addMessages([$message]);
        }

        return !$this->hasMessages();
    }
}
