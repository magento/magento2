<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category;

use Magento\Framework\Indexer\CacheContext;

/**
 * Category product indexer
 *
 * @api
 * @since 2.0.0
 */
class Product implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalog_category_product';

    /**
     * @var Product\Action\FullFactory
     * @since 2.0.0
     */
    protected $fullActionFactory;

    /**
     * @var Product\Action\RowsFactory
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
    protected $cacheContext;

    /**
     * @param Product\Action\FullFactory $fullActionFactory
     * @param Product\Action\RowsFactory $rowsActionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @since 2.0.0
     */
    public function __construct(
        Product\Action\FullFactory $fullActionFactory,
        Product\Action\RowsFactory $rowsActionFactory,
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
        $this->executeAction($ids);
        $this->registerEntities($ids);
    }

    /**
     * Add entities to cache context
     *
     * @param int[] $ids
     * @return void
     * @since 2.1.0
     */
    protected function registerEntities($ids)
    {
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
        $this->fullActionFactory->create()->execute();
        $this->registerTags();
    }

    /**
     * Add tags to cache context
     *
     * @return void
     * @since 2.1.0
     */
    protected function registerTags()
    {
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
        $this->executeAction($ids);
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
        $this->executeAction([$id]);
    }

    /**
     * Execute action for single entity or list of entities
     *
     * @param int[] $ids
     * @return $this
     * @since 2.0.0
     */
    protected function executeAction($ids)
    {
        $ids = array_unique($ids);
        $indexer = $this->indexerRegistry->get(static::INDEXER_ID);

        /** @var Product\Action\Rows $action */
        $action = $this->rowsActionFactory->create();
        if ($indexer->isWorking()) {
            $action->execute($ids, true);
        }
        $action->execute($ids);

        return $this;
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
