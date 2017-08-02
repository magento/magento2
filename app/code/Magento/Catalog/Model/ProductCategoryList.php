<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;

/**
 * Provides info about product categories.
 * @since 2.2.0
 */
class ProductCategoryList
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $categoryIdList = [];

    /**
     * @var ResourceModel\Product
     * @since 2.2.0
     */
    private $productResource;

    /**
     * @var ResourceModel\Category
     * @since 2.2.0
     */
    private $category;

    /**
     * @param ResourceModel\Product $productResource
     * @param ResourceModel\Category $category
     * @since 2.2.0
     */
    public function __construct(
        ResourceModel\Product $productResource,
        ResourceModel\Category $category
    ) {
        $this->productResource = $productResource;
        $this->category = $category;
    }

    /**
     * Retrieve category id list where product is present.
     *
     * @param int $productId
     * @return array
     * @since 2.2.0
     */
    public function getCategoryIds($productId)
    {
        if (!isset($this->categoryIdList[$productId])) {
            $unionSelect = new UnionExpression(
                [
                    $this->getCategorySelect($productId, $this->category->getCategoryProductTable()),
                    $this->getCategorySelect(
                        $productId,
                        $this->productResource->getTable(AbstractAction::MAIN_INDEX_TABLE)
                    )
                ],
                Select::SQL_UNION_ALL
            );

            $this->categoryIdList[$productId] = $this->productResource->getConnection()->fetchCol($unionSelect);
        }

        return $this->categoryIdList[$productId];
    }

    /**
     * Returns DB select.
     *
     * @param int $productId
     * @param string $tableName
     * @return Select
     * @since 2.2.0
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
}
