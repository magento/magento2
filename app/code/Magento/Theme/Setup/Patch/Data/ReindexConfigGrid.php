<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup\Patch\Data;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Theme\Model\Data\Design\Config;

/**
 * Perform a full reindex to ensure the grid table exists
 */
class ReindexConfigGrid implements DataPatchInterface
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * ReindexConfigGrid constructor
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $indexer = $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID);
        $indexer->reindexAll();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
