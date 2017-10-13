<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ValidatorHandler\ValidatorHandlerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CronException;

/**
 * Cron expression part validatorHandler factory
 *
 * @api
 */
class ValidatorHandlerFactory
{
    const ASTERISK_VALIDATION_HANDLER = 'Asterisk';
    const QUESTION_MARK_VALIDATION_HANDLER = 'QuestionMark';
    const MODULUS_VALIDATION_HANDLER = 'Modulus';
    const ASTERISK_MODULUS_VALIDATION_HANDLER = 'AsteriskModulus';
    const QUESTION_MARK_MODULUS_VALIDATION_HANDLER = 'QuestionMarkModulus';
    const RANGE_VALIDATION_HANDLER = 'Range';
    const REGULAR_VALIDATION_HANDLER = 'Regular';
    const LAST_VALIDATION_HANDLER = 'Last';
    const LAST_WEEKDAY_VALIDATION_HANDLER = 'LastWeekDay';
    const HASH_VALIDATION_HANDLER = 'Hash';
    const NEAREST_WEEKDAY_VALIDATION_HANDLER = 'NearestWeekDay';

    /**
     * @return array
     */
    public function getAvailableValidatorHandlers()
    {
        return [
            self::ASTERISK_VALIDATION_HANDLER,
            self::QUESTION_MARK_VALIDATION_HANDLER,
            self::MODULUS_VALIDATION_HANDLER,
            self::ASTERISK_MODULUS_VALIDATION_HANDLER,
            self::QUESTION_MARK_MODULUS_VALIDATION_HANDLER,
            self::RANGE_VALIDATION_HANDLER,
            self::REGULAR_VALIDATION_HANDLER,
            self::LAST_VALIDATION_HANDLER,
            self::LAST_WEEKDAY_VALIDATION_HANDLER,
            self::HASH_VALIDATION_HANDLER,
            self::NEAREST_WEEKDAY_VALIDATION_HANDLER,
        ];
    }

    /**
     * Get the validatorHandler specified by validatorHandler type
     *
     * @param string $validatorHandlerType
     *
     * @throws CronException
     * @return ValidatorHandlerInterface
     */
    public function create($validatorHandlerType)
    {
        if (!in_array($validatorHandlerType, $this->getAvailableValidatorHandlers())) {
            throw new CronException(
                __('Invalid cron expression part validator handler type: %1', $validatorHandlerType)
            );
        }

        $validatorHandler = ObjectManager::getInstance()
            ->get(
                'Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ValidatorHandler\\'
                . $validatorHandlerType
            );

        if (!$validatorHandler instanceof ValidatorHandlerInterface) {
            $exceptionMessage = 'Invalid cron expression part validator handler type: %1 is not an instance of '
                . ValidatorHandlerInterface::class;
            throw new CronException(__($exceptionMessage, $validatorHandlerType));
        }

        return $validatorHandler;
    }
}
