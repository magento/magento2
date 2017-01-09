<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;

class CategoryProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /** @var CategoryUrlRewriteGenerator */
    private $categoryUrlRewriteGenerator;

    /** @var UrlRewriteBunchReplacer */
    private $urlRewriteBunchReplacer;

    /** @var UrlRewriteHandler */
    private $urlRewriteHandler;

    /** @var DatabaseMapPool */
    private $databaseMapPool;

    /** @var string[] */
    private $dataUrlRewriteClassNames;

    /**
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlRewriteHandler $urlRewriteHandler
     * @param UrlRewriteBunchReplacer $urlRewriteBunchReplacer
     * @param DatabaseMapPool $databaseMapPool
     * @param string[] $dataUrlRewriteClassNames
     */
    public function __construct(
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlRewriteHandler $urlRewriteHandler,
        UrlRewriteBunchReplacer $urlRewriteBunchReplacer,
        DatabaseMapPool $databaseMapPool,
        array $dataUrlRewriteClassNames = [
            DataCategoryUrlRewriteDatabaseMap::class,
            DataProductUrlRewriteDatabaseMap::class
        ]
    ) {
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlRewriteHandler = $urlRewriteHandler;
        $this->urlRewriteBunchReplacer = $urlRewriteBunchReplacer;
        $this->databaseMapPool = $databaseMapPool;
        $this->dataUrlRewriteClassNames = $dataUrlRewriteClassNames;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getData('category');
        if ($category->getParentId() == Category::TREE_ROOT_ID) {
            return;
        }
        if ($category->dataHasChangedFor('url_key')
            || $category->dataHasChangedFor('is_anchor')
            || $category->getIsChangedProductList()
        ) {
            $this->initializeUrlRewritesDataMaps($category);

            $categoryUrlRewriteResult = $this->categoryUrlRewriteGenerator->generate($category);
            $this->urlRewriteBunchReplacer->doBunchReplace($categoryUrlRewriteResult);

            $productUrlRewriteResult = $this->urlRewriteHandler->generateProductUrlRewrites($category);
            $this->urlRewriteBunchReplacer->doBunchReplace($productUrlRewriteResult);

            $this->resetUrlRewritesDataMaps($category);
        }
    }

    /**
     * Initializes data maps to be further used
     *
     * @param Category $category
     * @return void
     */
    private function initializeUrlRewritesDataMaps($category)
    {
        foreach ($this->dataUrlRewriteClassNames as $className) {
            $this->databaseMapPool->getDataMap($className, $category->getEntityId());
        }

    }

    /**
     * Resets used data maps to free up memory and temporary tables
     *
     * @param Category $category
     * @return void
     */
    private function resetUrlRewritesDataMaps($category)
    {
        foreach ($this->dataUrlRewriteClassNames as $className) {
            $this->databaseMapPool->resetMap($className, $category->getEntityId());
        }

    }
}
