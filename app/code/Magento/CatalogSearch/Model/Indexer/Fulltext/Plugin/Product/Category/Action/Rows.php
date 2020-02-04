<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @return ActionRows
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(ActionRows $subject, ActionRows $result, array $entityIds): ActionRows
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
