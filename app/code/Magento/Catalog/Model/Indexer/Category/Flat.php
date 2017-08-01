<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category;

use Magento\Framework\Indexer\CacheContext;

/**
 * Category flat indexer
 *
 * @api
 * @since 2.0.0
 */
class Flat implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\Action\FullFactory
     * @since 2.0.0
     */
    protected $fullActionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\Action\RowsFactory
     * @since 2.0.0
     */
    protected $rowsActionFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     * @since 2.0.0
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     * @since 2.1.0
     */
    private $cacheContext;

    /**
     * @param Flat\Action\FullFactory $fullActionFactory
     * @param Flat\Action\RowsFactory $rowsActionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @since 2.0.0
     */
    public function __construct(
        Flat\Action\FullFactory $fullActionFactory,
        Flat\Action\RowsFactory $rowsActionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->fullActionFactory = $fullActionFactory;
        $this->rowsActionFactory = $rowsActionFactory;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @since 2.0.0
     */
    public function execute($ids)
    {
        $indexer = $this->indexerRegistry->get(Flat\State::INDEXER_ID);
        if ($indexer->isInvalid()) {
            return;
        }

        /** @var Flat\Action\Rows $action */
        $action = $this->rowsActionFactory->create();
        if ($indexer->isWorking()) {
            $action->reindex($ids, true);
        }
        $action->reindex($ids);
        $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Category::CACHE_TAG, $ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @since 2.0.0
     */
    public function executeFull()
    {
        $this->fullActionFactory->create()->reindexAll();
        $this->getCacheContext()->registerTags([\Magento\Catalog\Model\Category::CACHE_TAG]);
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     * @since 2.0.0
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     * @since 2.0.0
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }

    /**
     * Get cache context
     *
     * @return \Magento\Framework\Indexer\CacheContext
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    protected function getCacheContext()
    {
        if (!($this->cacheContext instanceof CacheContext)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(CacheContext::class);
        } else {
            return $this->cacheContext;
        }
    }
}
