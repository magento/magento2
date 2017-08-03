<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;

/**
 * Class \Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\Move
 *
 */
class Move
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var ChildrenCategoriesProvider
     * @since 2.2.0
     */
    private $childrenCategoriesProvider;

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
     * Perform url updating for children categories
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param \Magento\Catalog\Model\ResourceModel\Category $result
     * @param Category $category
     * @param Category $newParent
     * @param null|int $afterCategoryId
     * @return \Magento\Catalog\Model\ResourceModel\Category
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterChangeParent(
        \Magento\Catalog\Model\ResourceModel\Category $subject,
        \Magento\Catalog\Model\ResourceModel\Category $result,
        Category $category,
        Category $newParent,
        $afterCategoryId
    ) {
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
