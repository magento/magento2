<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * Indexer(s) configuration
 *
 * @api
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Get indexers list
     *
     * @return array[]
     * @since 2.0.0
     */
    public function getIndexers();

    /**
     * Get indexer by ID
     *
     * @param string $indexerId
     * @return array
     * @since 2.0.0
     */
    public function getIndexer($indexerId);
}
