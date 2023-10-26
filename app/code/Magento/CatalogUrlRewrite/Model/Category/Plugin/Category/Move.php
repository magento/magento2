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
use Magento\Store\Model\StoreManagerInterface;

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
     * @var  StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param CategoryFactory $categoryFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
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
            $this->removeObsoleteUrlPathEntries($category);
            $this->updateCategoryUrlKeyForStore($category);
            $category->unsUrlPath();
            $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
            $category->getResource()->saveAttribute($category, 'url_path');
            $this->updateUrlPathForChildren($category);
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
     * Updates url_path for child categories.
     *
     * @param Category $category
     * @return void
     */
    private function updateUrlPathForChildren(Category $category): void
    {
        foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
            $childCategory->setStoreId($category->getStoreId());
            $childCategory->unsUrlPath();
            $childCategory->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($childCategory));
            $childCategory->getResource()->saveAttribute($childCategory, 'url_path');
        }
    }

    /**
     * Clean obsolete entries
     *
     * @param Category $category
     * @return void
     */
    private function removeObsoleteUrlPathEntries(Category $category): void
    {
        if ($this->storeManager->hasSingleStore()) {
            return;
        }
        $origPath = $category->getOrigData('path');
        $path = $category->getData('path');
        if ($origPath != null && $path != null && $origPath != $path) {
            $category->unsUrlPath();
            $category->getResource()->saveAttribute($category, 'url_path');
            foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
                $childCategory->unsUrlPath();
                $childCategory->getResource()->saveAttribute($childCategory, 'url_path');
            }
        }
    }
}
