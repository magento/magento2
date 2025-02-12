<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\ContextInterface;

/**
 * Logs requests
 */
interface RequestLoggerInterface
{
    /**
     * Configuration path to logger type
     */
    public const CONFIG_PATH_BACKPRESSURE_LOGGER = 'backpressure/logger/type';

    /**
     * Increase counter for requests coming inside given timeslot from given identity
     *
     * @param ContextInterface $context
     * @param int $timeSlot Time slot to increase the counter for (timestamp)
     * @param int $discardAfter Counter for the time slot can be discarded after given number of seconds
     * @return int Requests logged for the identity and the time slot
     */
    public function incrAndGetFor(ContextInterface $context, int $timeSlot, int $discardAfter): int;

    /**
     * Get counter for specific identity and time slot
     *
     * @param ContextInterface $context
     * @param int $timeSlot
     * @return int|null
     */
    public function getFor(ContextInterface $context, int $timeSlot): ?int;
}
