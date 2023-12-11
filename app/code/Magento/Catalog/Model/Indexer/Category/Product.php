<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\IndexMutexException;
use Magento\Framework\Indexer\IndexMutexInterface;

/**
 * Category product indexer
 *
 * @api
 * @since 100.0.2
 */
class Product implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalog_category_product';

    /**
     * @var Product\Action\FullFactory
     */
    protected $fullActionFactory;

    /**
     * @var Product\Action\RowsFactory
     */
    protected $rowsActionFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     * @since 100.0.11
     */
    protected $cacheContext;

    /**
     * @var IndexMutexInterface
     */
    private $indexMutex;

    /**
     * @param Product\Action\FullFactory $fullActionFactory
     * @param Product\Action\RowsFactory $rowsActionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param IndexMutexInterface|null $indexMutex
     */
    public function __construct(
        Product\Action\FullFactory $fullActionFactory,
        Product\Action\RowsFactory $rowsActionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        ?IndexMutexInterface $indexMutex = null
    ) {
        $this->fullActionFactory = $fullActionFactory;
        $this->rowsActionFactory = $rowsActionFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->indexMutex = $indexMutex ?? ObjectManager::getInstance()->get(IndexMutexInterface::class);
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @throws IndexMutexException
     */
    public function execute($ids)
    {
        $this->registerEntities($ids);
        $this->executeAction($ids);
    }

    /**
     * Add entities to cache context
     *
     * @param int[] $ids
     * @return void
     * @since 100.0.11
     */
    protected function registerEntities($ids)
    {
        $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Category::CACHE_TAG, $ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @throws IndexMutexException
     */
    public function executeFull()
    {
        $this->indexMutex->execute(
            static::INDEXER_ID,
            function () {
                $this->fullActionFactory->create()->execute();
                $this->registerTags();
            }
        );
    }

    /**
     * Add tags to cache context
     *
     * @return void
     * @since 100.0.11
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
     * @throws IndexMutexException
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
     * @throws IndexMutexException
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
     * @throws IndexMutexException
     */
    protected function executeAction($ids)
    {
        $ids = array_unique($ids);
        $indexer = $this->indexerRegistry->get(static::INDEXER_ID);

        if ($indexer->isScheduled()) {
            $this->indexMutex->execute(
                static::INDEXER_ID,
                function () use ($ids) {
                    $this->rowsActionFactory->create()->execute($ids, true);
                }
            );
        } else {
            $this->rowsActionFactory->create()->execute($ids);
        }

        return $this;
    }

    /**
     * Get cache context
     *
     * @return \Magento\Framework\Indexer\CacheContext
     * @deprecated 100.0.11
     * @since 100.0.11
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
