<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;

class Move
{
    /** @var CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /**
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param callable $proceed
     * @param Category $category
     * @param Category $newParent
     * @param null|int $afterCategoryId
     * @return callable
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundChangeParent(
        \Magento\Catalog\Model\ResourceModel\Category $subject,
        \Closure $proceed,
        $category,
        $newParent,
        $afterCategoryId
    ) {
        $result = $proceed($category, $newParent, $afterCategoryId);
        $category->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
        $category->getResource()->saveAttribute($category, 'url_path');
        $this->updateUrlPathForChildren($category);

        return $result;
    }

    /**
     * @param Category $category
     * @return void
     */
    protected function updateUrlPathForChildren($category)
    {
        foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
            $childCategory->unsUrlPath();
            $childCategory->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($childCategory));
            $childCategory->getResource()->saveAttribute($childCategory, 'url_path');
        }
    }
}
