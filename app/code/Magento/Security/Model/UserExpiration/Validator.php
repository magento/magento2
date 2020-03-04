<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\UserExpiration;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Validates that the expires_at field is later than the current date/time.
 */
class Validator extends AbstractValidator
{

    /**@var TimezoneInterface */
    private $timezone;

    /**@var DateTime */
    private $dateTime;

    /**
     * Validator constructor.
     *
     * @param TimezoneInterface $timezone
     * @param DateTime $dateTime
     */
    public function __construct(
        TimezoneInterface $timezone,
        DateTime $dateTime
    ) {
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
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
            if (strtotime($expiresAt)) {
                $currentTime = $this->dateTime->gmtTimestamp();
                $utcExpiresAt = $this->timezone->convertConfigTimeToUtc($expiresAt);
                $expiresAt = $this->timezone->date($utcExpiresAt)->getTimestamp();
                if ($expiresAt < $currentTime) {
                    $messages['expires_at'] = __('"%1" must be later than the current date.', $label);
                }
            } else {
                $messages['expires_at'] = __('"%1" is not a valid date.', $label);
            }
        }
        $this->_addMessages($messages);

        return empty($messages);
    }
}
