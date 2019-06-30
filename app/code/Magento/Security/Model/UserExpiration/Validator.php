<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\UserExpiration;

use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 *
 * @package Magento\Security\Model\Validator
 */
class Validator extends AbstractValidator
{

    /**
     * Ensure that the given date is later than the current date.
     *
     * @param string $value
     * @return bool
     * @throws \Exception
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $messages = [];
        $expiresAt = $value;
        $label = 'Expiration date';
        if (\Zend_Validate::is($expiresAt, 'NotEmpty')) {
            $currentTime = new \DateTime();
            $expiresAt = new \DateTime($expiresAt);

            if ($expiresAt < $currentTime) {
                $messages['expires_at'] = __('"%1" must be later than the current date.', $label);
            }
        }
        $this->_addMessages($messages);

        return empty($messages);
    }
}
