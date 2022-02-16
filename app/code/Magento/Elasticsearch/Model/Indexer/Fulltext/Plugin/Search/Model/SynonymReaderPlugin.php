<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Indexer\Fulltext\Plugin\Search\Model;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Search\Model\ResourceModel\SynonymGroup;

class SynonymReaderPlugin
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(IndexerRegistry $indexerRegistry) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @param SynonymGroup $subject
     * @param AbstractDb $synonymGroup
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(SynonymGroup $subject, AbstractDb $synonymGroup) {
        $this->invalidateIndexer();
    }

    /**
     * @param SynonymGroup $subject
     * @param AbstractDb $synonymGroup
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(SynonymGroup $subject, AbstractDb $synonymGroup) {
        $this->invalidateIndexer();
    }

    /**
     * Invalidate fulltext indexer
     *
     * @return void
     */
    private function invalidateIndexer() {
        $fulltextIndexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        if (!$fulltextIndexer->isScheduled()) {
            $fulltextIndexer->invalidate();
        }
    }
}
