<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Config;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Provides an information about indexers dependencies.
 */
interface DependencyInfoProviderInterface
{
    /**
     * Returns Indexer Ids on which the current indexer depends directly.
     *
     * @param string $indexerId
     * @return string[]
     * @throws NoSuchEntityException In case when the indexer with the specified Id does not exist.
     */
    public function getIndexerIdsToRunBefore(string $indexerId): array;

    /**
     * Returns the list of Indexer Ids which directly depend on the current indexer.
     *
     * @param string $indexerId
     * @return string[]
     * @throws NoSuchEntityException In case when the indexer with the specified Id does not exist.
     */
    public function getIndexerIdsToRunAfter(string $indexerId): array;
}
