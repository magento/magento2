<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\Event\Observer;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;

class CategoryUrlPathAutogenerator
{
    /** @var CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

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
     * @param Observer $observer
     * @return void
     */
    public function invoke(Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        if ($category->getUrlKey() !== false) {
            $category->setUrlKey($this->categoryUrlPathGenerator->generateUrlKey($category))
                ->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
            if (!$category->isObjectNew()) {
                $category->getResource()->saveAttribute($category, 'url_path');
                if ($category->dataHasChangedFor('url_path')) {
                    $this->updateUrlPathForChildren($category);
                }
            }
        }
    }

    /**
     * @param Category $category
     * @return void
     */
    protected function updateUrlPathForChildren(Category $category)
    {
        foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
            $childCategory->unsUrlPath();
            $childCategory->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($childCategory));
            $childCategory->getResource()->saveAttribute($childCategory, 'url_path');
        }
    }
}
