<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CronException;

/**
 * Cron expression part factory
 *
 * @api
 */
class PartFactory
{
    const GENERIC_PART = 'Generic';

    /**
     * Get available indexes, and its mapping with index part classes for factories
     *
     * @return array
     */
    public function getPartAvailableIndexes()
    {
        return [
            0 => 'Minutes',
            1 => 'Hours',
            2 => 'MonthDay',
            3 => 'Month',
            4 => 'WeekDay',
            5 => 'Year',
        ];
    }

    /**
     * Create an expression part object
     *
     * @param int|string $partIndex
     * @param string     $partValue
     *
     * @throws CronException
     * @return PartInterface
     */
    public function create($partIndex, $partValue)
    {
        $availableIndexes = $this->getPartAvailableIndexes();
        if (array_key_exists($partIndex, $availableIndexes)) {
            $indexType = $availableIndexes[$partIndex];
        }

        if (!isset($indexType) && $partIndex == self::GENERIC_PART) {
            $indexType = $partIndex;
        }

        if (!isset($indexType)) {
            throw new CronException(__('Invalid cron expression part index: %1', $partIndex));
        }

        /** @var Part $part */
        $part = ObjectManager::getInstance()
            ->create('Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Index\\' . $indexType);

        if (!$part instanceof PartInterface) {
            $exceptionMessage = 'Invalid cron expression part index: %1 is not an instance of '
                . PartInterface::class;
            throw new CronException(__($exceptionMessage, $indexType));
        }

        $part->setPartValue($partValue);

        return $part;
    }
}
