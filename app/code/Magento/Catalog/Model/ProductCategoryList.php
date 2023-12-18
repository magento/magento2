<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;

/**
 * Provides info about product categories.
 */
class ProductCategoryList implements ResetAfterRequestInterface
{
    /**
     * @var array
     */
    private $categoryIdList = [];

    /**
     * @var ResourceModel\Product
     */
    private $productResource;

    /**
     * @var ResourceModel\Category
     */
    private $category;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @param ResourceModel\Product $productResource
     * @param ResourceModel\Category $category
     * @param StoreManagerInterface $storeManager
     * @param TableMaintainer|null $tableMaintainer
     */
    public function __construct(
        ResourceModel\Product $productResource,
        ResourceModel\Category $category,
        StoreManagerInterface $storeManager = null,
        TableMaintainer $tableMaintainer = null
    ) {
        $this->productResource = $productResource;
        $this->category = $category;
        $this->tableMaintainer = $tableMaintainer ?: ObjectManager::getInstance()->get(TableMaintainer::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * Retrieve category id list where product is present.
     *
     * @param int $productId
     * @return array
     */
    public function getCategoryIds($productId)
    {
        if (!isset($this->categoryIdList[$productId])) {
            $unionTables[] = $this->getCategorySelect($productId, $this->category->getCategoryProductTable());
            foreach ($this->storeManager->getStores() as $store) {
                $unionTables[] = $this->getCategorySelect(
                    $productId,
                    $this->tableMaintainer->getMainTable($store->getId())
                );
            }
            $unionSelect = new UnionExpression(
                $unionTables,
                Select::SQL_UNION_ALL
            );

            $this->categoryIdList[$productId] = array_map(
                'intval',
                $this->productResource->getConnection()->fetchCol($unionSelect)
            );
        }

        return $this->categoryIdList[$productId];
    }

    /**
     * Returns DB select.
     *
     * @param int $productId
     * @param string $tableName
     * @return Select
     */
    public function getCategorySelect($productId, $tableName)
    {
        return $this->productResource->getConnection()->select()->from(
            $tableName,
            ['category_id']
        )->where(
            'product_id = ?',
            $productId
        );
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->categoryIdList = [];
    }
}
