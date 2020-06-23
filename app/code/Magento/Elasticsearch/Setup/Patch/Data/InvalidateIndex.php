<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Elasticsearch\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

/**
 * Invalidate fulltext index
 */
class InvalidateIndex implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup, IndexerRegistry $indexerRegistry)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID)->invalidate();
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
