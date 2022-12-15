<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

use Magento\Framework\Indexer\IndexMutexInterface;

/**
 * Product Categories indexer
 *
 * @api
 * @since 100.0.2
 */
class Category extends \Magento\Catalog\Model\Indexer\Category\Product
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalog_product_category';

    /**
     * @param \Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory $fullActionFactory
     * @param Category\Action\RowsFactory $rowsActionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param IndexMutexInterface|null $indexMutex
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory $fullActionFactory,
        Category\Action\RowsFactory $rowsActionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        ?IndexMutexInterface $indexMutex = null
    ) {
        //phpcs:enable
        parent::__construct($fullActionFactory, $rowsActionFactory, $indexerRegistry, $indexMutex);
    }

    /**
     * Add tags to cache context
     *
     * @return void
     * @since 100.0.11
     */
    protected function registerTags()
    {
        $this->getCacheContext()->registerTags(
            [
                \Magento\Catalog\Model\Category::CACHE_TAG,
                \Magento\Catalog\Model\Product::CACHE_TAG
            ]
        );
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
        $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
    }
}
