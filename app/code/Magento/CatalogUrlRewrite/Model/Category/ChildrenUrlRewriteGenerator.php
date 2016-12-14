<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlRewritesSet;
use Magento\Framework\App\ObjectManager;

class ChildrenUrlRewriteGenerator
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory */
    protected $categoryUrlRewriteGeneratorFactory;

    /** @var \Magento\UrlRewrite\Model\UrlRewritesSet */
    private $urlRewritesSet;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory $categoryUrlRewriteGeneratorFactory
     * @param UrlRewritesSet|null $urlRewritesSet
     */
    public function __construct(
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        CategoryUrlRewriteGeneratorFactory $categoryUrlRewriteGeneratorFactory,
        UrlRewritesSet $urlRewritesSet = null
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGeneratorFactory = $categoryUrlRewriteGeneratorFactory;
        $this->urlRewritesSet = $urlRewritesSet ?: ObjectManager::getInstance()->get(UrlRewritesSet::class);
    }

    /**
     * Generate list of children urls
     *
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category $category
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate($storeId, Category $category, $rootCategoryId = null)
    {
        foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
            $childCategory->setStoreId($storeId);
            $childCategory->setData('save_rewrites_history', $category->getData('save_rewrites_history'));
            /** @var CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator */
            $categoryUrlRewriteGenerator = $this->categoryUrlRewriteGeneratorFactory->create();
            $this->urlRewritesSet->merge($categoryUrlRewriteGenerator->generate($childCategory, false, $rootCategoryId));
        }

        $result = $this->urlRewritesSet->getData();
        $this->urlRewritesSet->resetData();
        return $result;
    }
}
