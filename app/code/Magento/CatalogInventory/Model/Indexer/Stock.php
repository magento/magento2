<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Indexer;

use Magento\Framework\Indexer\CacheContext;

/**
 * Class \Magento\CatalogInventory\Model\Indexer\Stock
 *
 * @since 2.0.0
 */
class Stock implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Row
     * @since 2.0.0
     */
    protected $_productStockIndexerRow;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows
     * @since 2.0.0
     */
    protected $_productStockIndexerRows;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Full
     * @since 2.0.0
     */
    protected $_productStockIndexerFull;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     * @since 2.1.0
     */
    private $cacheContext;

    /**
     * @param Stock\Action\Row $productStockIndexerRow
     * @param Stock\Action\Rows $productStockIndexerRows
     * @param Stock\Action\Full $productStockIndexerFull
     * @since 2.0.0
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Indexer\Stock\Action\Row $productStockIndexerRow,
        \Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows $productStockIndexerRows,
        \Magento\CatalogInventory\Model\Indexer\Stock\Action\Full $productStockIndexerFull
    ) {
        $this->_productStockIndexerRow = $productStockIndexerRow;
        $this->_productStockIndexerRows = $productStockIndexerRows;
        $this->_productStockIndexerFull = $productStockIndexerFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     *
     * @return void
     * @since 2.0.0
     */
    public function execute($ids)
    {
        $this->_productStockIndexerRows->execute($ids);
        $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @since 2.0.0
     */
    public function executeFull()
    {
        $this->_productStockIndexerFull->execute();
        $this->getCacheContext()->registerTags(
            [
                \Magento\Catalog\Model\Category::CACHE_TAG,
                \Magento\Catalog\Model\Product::CACHE_TAG
            ]
        );
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     *
     * @return void
     * @since 2.0.0
     */
    public function executeList(array $ids)
    {
        $this->_productStockIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     * @since 2.0.0
     */
    public function executeRow($id)
    {
        $this->_productStockIndexerRow->execute($id);
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
