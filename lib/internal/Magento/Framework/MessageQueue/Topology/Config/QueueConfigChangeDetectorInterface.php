<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Detects whether message-queue topology config is out of sync with persisted queue registry.
 *
 * @api
 */
interface QueueConfigChangeDetectorInterface
{
    /**
     * Check whether configured queues are missing from the persisted registry.
     *
     * @return bool
     */
    public function hasChanges(): bool;

    /**
     * Return queue names present in topology config but missing from the persisted registry.
     *
     * @return string[]
     */
    public function getMissingQueues(): array;
}
