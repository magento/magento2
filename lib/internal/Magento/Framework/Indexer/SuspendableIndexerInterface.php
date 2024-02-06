<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
 * Interface for indexers that can be suspended
 */
interface SuspendableIndexerInterface extends IndexerInterface
{
    /**
     * Check whether indexer is suspended
     *
     * @return bool
     */
    public function isSuspended(): bool;
}
