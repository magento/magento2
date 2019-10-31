<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Product\Category\Action;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\Catalog\Model\Indexer\Product\Category\Action\Rows as ActionRows;

/**
 * Catalog search indexer plugin for catalog category products assignment
 */
class Rows
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex after catalog category product reindex
     *
     * @param ActionRows $subject
     * @param ActionRows $result
     * @param array $entityIds
     * @param boolean $useTempTable
     * @return Rows
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(ActionRows $subject, ActionRows $result, array $entityIds, $useTempTable)
    {
        if (!empty($entityIds)) {
            $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);
            if ($indexer->isScheduled()) {
                $indexer->reindexList($entityIds);
            }
        }
        return $result;
    }
}
