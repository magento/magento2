<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

use Magento\Framework\Indexer\CacheContext;

/**
 * Class \Magento\Catalog\Model\Indexer\Product\Price
 *
 * @since 2.0.0
 */
class Price implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Action\Row
     * @since 2.0.0
     */
    protected $_productPriceIndexerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Action\Rows
     * @since 2.0.0
     */
    protected $_productPriceIndexerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Action\Full
     * @since 2.0.0
     */
    protected $_productPriceIndexerFull;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     * @since 2.1.0
     */
    private $cacheContext;

    /**
     * @param Price\Action\Row $productPriceIndexerRow
     * @param Price\Action\Rows $productPriceIndexerRows
     * @param Price\Action\Full $productPriceIndexerFull
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Price\Action\Row $productPriceIndexerRow,
        \Magento\Catalog\Model\Indexer\Product\Price\Action\Rows $productPriceIndexerRows,
        \Magento\Catalog\Model\Indexer\Product\Price\Action\Full $productPriceIndexerFull
    ) {
        $this->_productPriceIndexerRow = $productPriceIndexerRow;
        $this->_productPriceIndexerRows = $productPriceIndexerRows;
        $this->_productPriceIndexerFull = $productPriceIndexerFull;
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
        $this->_productPriceIndexerRows->execute($ids);
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
        $this->_productPriceIndexerFull->execute();
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
     * @since 2.0.0
     */
    public function executeList(array $ids)
    {
        $this->_productPriceIndexerRows->execute($ids);
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
        $this->_productPriceIndexerRow->execute($id);
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
