<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\Store\Model\Store;

/**
 * Perform url updating for children categories.
 */
class Move
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var ChildrenCategoriesProvider
     */
    private $childrenCategoriesProvider;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        CategoryFactory $categoryFactory
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Perform url updating for children categories
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param \Magento\Catalog\Model\ResourceModel\Category $result
     * @param Category $category
     * @param Category $newParent
     * @param null|int $afterCategoryId
     * @return \Magento\Catalog\Model\ResourceModel\Category
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterChangeParent(
        \Magento\Catalog\Model\ResourceModel\Category $subject,
        \Magento\Catalog\Model\ResourceModel\Category $result,
        Category $category,
        Category $newParent,
        $afterCategoryId
    ) {
        $categoryStoreId = $category->getStoreId();
        foreach ($category->getStoreIds() as $storeId) {
            $category->setStoreId($storeId);
            if (!$this->isGlobalScope($storeId)) {
                $this->updateCategoryUrlKeyForStore($category);
                $category->unsUrlPath();
                $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
                $category->getResource()->saveAttribute($category, 'url_path');
                $this->updateUrlPathForChildren($category);
            }
        }
        $category->setStoreId($categoryStoreId);

        return $result;
    }

    /**
     * Set category url_key according to current category store id.
     *
     * @param Category $category
     * @return void
     */
    private function updateCategoryUrlKeyForStore(Category $category)
    {
        $item = $this->categoryFactory->create();
        $item->setStoreId($category->getStoreId());
        $item->load($category->getId());
        $category->setUrlKey($item->getUrlKey());
    }

    /**
     * Check is global scope.
     *
     * @param int|null $storeId
     * @return bool
     */
    private function isGlobalScope($storeId)
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }

    /**
     * Updates url_path for child categories.
     *
     * @param Category $category
     * @return void
     */
    private function updateUrlPathForChildren($category)
    {
        foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
            $childCategory->setStoreId($category->getStoreId());
            $childCategory->unsUrlPath();
            $childCategory->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($childCategory));
            $childCategory->getResource()->saveAttribute($childCategory, 'url_path');
        }
    }
}
