<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved
 */
namespace Magento\CatalogInventory\Model\Indexer;

use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\App\ObjectManager;

class Stock implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var Row
     */
    protected $_productStockIndexerRow;

    /**
     * @var Rows
     */
    protected $_productStockIndexerRows;

    /**
     * @var Full
     */
    protected $_productStockIndexerFull;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @param Stock\Action\Row $productStockIndexerRow
     * @param Stock\Action\Rows $productStockIndexerRows
     * @param Stock\Action\Full $productStockIndexerFull
     * @param CacheContext|null $cacheContext
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Indexer\Stock\Action\Row $productStockIndexerRow,
        \Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows $productStockIndexerRows,
        \Magento\CatalogInventory\Model\Indexer\Stock\Action\Full $productStockIndexerFull,
        ?CacheContext $cacheContext = null
    ) {
        $this->_productStockIndexerRow = $productStockIndexerRow;
        $this->_productStockIndexerRows = $productStockIndexerRows;
        $this->_productStockIndexerFull = $productStockIndexerFull;
        $this->cacheContext = $cacheContext ?: ObjectManager::getInstance()->get(CacheContext::class);
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
    }

    /**
     * Get cache context
     *
     * @return CacheContext
     * @deprecated 100.0.7
     * @see we don't add dependecies this way anymore
     */
    protected function getCacheContext()
    {
        if (!($this->cacheContext instanceof CacheContext)) {
            return ObjectManager::getInstance()->get(CacheContext::class);
        } else {
            return $this->cacheContext;
        }
    }
}
