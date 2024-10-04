<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
 * Interface for managing the suspended status of indexers.
 *
 * Allows for temporary suspension of indexer auto-updates by cron,
 * facilitating performance optimization during bulk operations.
 */
interface SuspendableIndexerInterface extends IndexerInterface
{
    /**
     * Determines if the indexer is suspended.
     *
     * @return bool True if suspended, false otherwise.
     */
    public function isSuspended(): bool;
}
