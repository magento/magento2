<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * Indexer(s) configuration
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Get indexers list
     *
     * @return array[]
     */
    public function getIndexers();

    /**
     * Get indexer by ID
     *
     * @param string $indexerId
     * @return array
     */
    public function getIndexer($indexerId);
}
