<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

use Magento\Framework\Indexer\CacheContext;

/**
 * Class \Magento\Catalog\Model\Indexer\Product\Flat
 *
 */
class Flat implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row
     */
    protected $_productFlatIndexerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows
     */
    protected $_productFlatIndexerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full
     */
    protected $_productFlatIndexerFull;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     * @since 2.0.11
     */
    private $cacheContext;

    /**
     * @param Flat\Action\Row $productFlatIndexerRow
     * @param Flat\Action\Rows $productFlatIndexerRows
     * @param Flat\Action\Full $productFlatIndexerFull
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row $productFlatIndexerRow,
        \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows $productFlatIndexerRows,
        \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full $productFlatIndexerFull
    ) {
        $this->_productFlatIndexerRow = $productFlatIndexerRow;
        $this->_productFlatIndexerRows = $productFlatIndexerRows;
        $this->_productFlatIndexerFull = $productFlatIndexerFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->_productFlatIndexerRows->execute($ids);
        $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->_productFlatIndexerFull->execute();
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
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->_productFlatIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->_productFlatIndexerRow->execute($id);
    }

    /**
     * Get cache context
     *
     * @return \Magento\Framework\Indexer\CacheContext
     * @deprecated 2.0.11
     * @since 2.0.11
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
