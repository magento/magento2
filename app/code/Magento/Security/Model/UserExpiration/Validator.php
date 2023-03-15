<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\UserExpiration;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\LocalizedDateToUtcConverterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\ValidatorChain;

/**
 * Validates that the expires_at field is later than the current date/time.
 */
class Validator extends AbstractValidator
{
    /**
     * @param TimezoneInterface $timezone
     * @param DateTime $dateTime
     * @param LocalizedDateToUtcConverterInterface|null $localizedDateToUtcConverter
     */
    public function __construct(
        private readonly TimezoneInterface $timezone,
        private readonly DateTime $dateTime,
        private ?LocalizedDateToUtcConverterInterface $localizedDateToUtcConverter = null
    ) {
        $this->localizedDateToUtcConverter = $localizedDateToUtcConverter ?: ObjectManager::getInstance()
            ->get(LocalizedDateToUtcConverterInterface::class);
    }

    /**
     * Ensure that the given date is later than the current date.
     *
     * @param string $value
     * @return bool
     * @throws Exception
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $messages = [];
        $label = 'Expiration date';
        if (ValidatorChain::is($value, NotEmpty::class)) {
            $utcExpiresAt = $this->localizedDateToUtcConverter->convertLocalizedDateToUtc($value);
            $currentTime = $this->dateTime->gmtTimestamp();
            $expiresAt = $this->timezone->date($utcExpiresAt)->getTimestamp();
            if ($expiresAt < $currentTime) {
                $messages['expires_at'] = __('"%1" must be later than the current date.', $label);
            }
        }
        $this->_addMessages($messages);

        return empty($messages);
    }
}
