<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

/**
 * Indexers configuration
 */
class Config implements ConfigInterface
{
    /**
     * @inheritDoc
     */
    public function getIndexers()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIndexer($indexerId)
    {
        return [];
    }
}
