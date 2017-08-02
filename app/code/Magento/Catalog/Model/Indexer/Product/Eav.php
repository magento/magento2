<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

use Magento\Framework\Indexer\CacheContext;

/**
 * Class \Magento\Catalog\Model\Indexer\Product\Eav
 *
 * @since 2.0.0
 */
class Eav implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Row
     * @since 2.0.0
     */
    protected $_productEavIndexerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows
     * @since 2.0.0
     */
    protected $_productEavIndexerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full
     * @since 2.0.0
     */
    protected $_productEavIndexerFull;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     * @since 2.1.0
     */
    private $cacheContext;

    /**
     * @param Eav\Action\Row $productEavIndexerRow
     * @param Eav\Action\Rows $productEavIndexerRows
     * @param Eav\Action\Full $productEavIndexerFull
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Eav\Action\Row $productEavIndexerRow,
        \Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows $productEavIndexerRows,
        \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full $productEavIndexerFull
    ) {
        $this->_productEavIndexerRow = $productEavIndexerRow;
        $this->_productEavIndexerRows = $productEavIndexerRows;
        $this->_productEavIndexerFull = $productEavIndexerFull;
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
        $this->_productEavIndexerRows->execute($ids);
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
        $this->_productEavIndexerFull->execute();
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
        $this->_productEavIndexerRows->execute($ids);
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
        $this->_productEavIndexerRow->execute($id);
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
