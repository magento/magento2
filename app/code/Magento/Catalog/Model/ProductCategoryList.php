<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use Magento\Indexer\Model\ResourceModel\FrontendResource;

/**
 * Provides info about product categories.
 */
class ProductCategoryList
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
     * @var FrontendResource
     */
    private $categoryProductIndexerFrontend;

    /**
     * @param ResourceModel\Product $productResource
     * @param ResourceModel\Category $category
     * @param FrontendResource $categoryProductIndexerFrontend
     */
    public function __construct(
        ResourceModel\Product $productResource,
        ResourceModel\Category $category,
        FrontendResource $categoryProductIndexerFrontend
    ) {
        $this->productResource = $productResource;
        $this->category = $category;
        $this->categoryProductIndexerFrontend = $categoryProductIndexerFrontend;
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
            $unionSelect = new UnionExpression(
                [
                    $this->getCategorySelect($productId, $this->category->getCategoryProductTable()),
                    $this->getCategorySelect($productId, $this->categoryProductIndexerFrontend->getMainTable())
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
