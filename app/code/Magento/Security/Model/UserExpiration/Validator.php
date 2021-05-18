<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\UserExpiration;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\AbstractValidator;
use Psr\Log\LoggerInterface;

/**
 * Validates that the expires_at field is later than the current date/time.
 */
class Validator extends AbstractValidator
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TimezoneInterface $timezone
     * @param DateTime $dateTime
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        TimezoneInterface $timezone,
        DateTime $dateTime,
        LoggerInterface $logger = null
    ) {
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
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
        $label = 'Expiration date';
        if (\Zend_Validate::is($value, 'NotEmpty')) {
            try {
                $expiresAt = $this->timezone->date($value);
                $currentTime = $this->dateTime->gmtTimestamp();
                $utcExpiresAt = $this->timezone->convertConfigTimeToUtc($expiresAt);
                $expiresAt = $this->timezone->date($utcExpiresAt)->getTimestamp();
                if ($expiresAt < $currentTime) {
                    $messages['expires_at'] = __('"%1" must be later than the current date.', $label);
                }
            } catch (\Exception $e) {
                $this->logger->error($e);
                $messages['expires_at'] = __('"%1" is not a valid date.', $label);
            }
        }
        $this->_addMessages($messages);

        return empty($messages);
    }
}
