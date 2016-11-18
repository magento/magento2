<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\ArrayMerger;
use Magento\Framework\App\ObjectManager;

class ChildrenUrlRewriteGenerator
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory */
    protected $categoryUrlRewriteGeneratorFactory;

    /** @var \Magento\UrlRewrite\Model\ArrayMerger */
    private $arrayMerger;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGeneratorFactory $categoryUrlRewriteGeneratorFactory
     * @param ArrayMerger|null $arrayMerger
     */
    public function __construct(
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        CategoryUrlRewriteGeneratorFactory $categoryUrlRewriteGeneratorFactory,
        ArrayMerger $arrayMerger = null
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGeneratorFactory = $categoryUrlRewriteGeneratorFactory;
        $this->arrayMerger = $arrayMerger ?: ObjectManager::getInstance()->get(ArrayMerger::class);
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
            $this->arrayMerger->addData($categoryUrlRewriteGenerator->generate($childCategory, false, $rootCategoryId));
        }
        return $this->arrayMerger->getResetData();
    }
}
