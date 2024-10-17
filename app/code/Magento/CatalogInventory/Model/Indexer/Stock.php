<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Indexer;

use Magento\Framework\Indexer\CacheContext;

class Stock implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Row
     */
    protected $_productStockIndexerRow;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows
     */
    protected $_productStockIndexerRows;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Full
     */
    protected $_productStockIndexerFull;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;

    /**
     * @param Stock\Action\Row $productStockIndexerRow
     * @param Stock\Action\Rows $productStockIndexerRows
     * @param Stock\Action\Full $productStockIndexerFull
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
     */
    public function execute($ids)
    {
        $this->_productStockIndexerRows->execute($ids);
        $this->getCacheContext()->registerEntities(ProductModel::CACHE_TAG, $ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
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
     */
    public function executeList(array $ids)
    {
        $this->_productStockIndexerRows->execute($ids);
        $this->getCacheContext()->registerEntities(ProductModel::CACHE_TAG, $ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     */
    public function executeRow($id)
    {
        $this->_productStockIndexerRow->execute($id);
        $this->getCacheContext()->registerEntities(ProductModel::CACHE_TAG, [$id]);
    }

    /**
     * Get cache context
     *
     * @return \Magento\Framework\Indexer\CacheContext
     * @deprecated 100.0.7
     * @see we don't add dependecies this way anymore
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
