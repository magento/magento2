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

    /**@var \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
    private $timezone;

    /**
     * Validator constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

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
            $currentTime = $this->timezone->date()->getTimestamp();
            $utcExpiresAt = $this->timezone->convertConfigTimeToUtc($expiresAt);
            $expiresAt = $this->timezone->date($utcExpiresAt)->getTimestamp();
            if ($expiresAt < $currentTime) {
                $messages['expires_at'] = __('"%1" must be later than the current date.', $label);
            }
        }
        $this->_addMessages($messages);

        return empty($messages);
    }
}
