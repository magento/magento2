<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogUrlRewrite\Model\Map\DataMapPoolInterface;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteMap;

class CategoryProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /** @var CategoryUrlRewriteGenerator */
    private $categoryUrlRewriteGenerator;

    /** @var UrlRewriteBunchReplacer */
    private $urlRewriteBunchReplacer;

    /** @var UrlRewriteHandler */
    private $urlRewriteHandler;

    /** @var DataMapPoolInterface */
    private $dataMapPoolInterface;

    /**
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlRewriteHandler $urlRewriteHandler
     * @param UrlRewriteBunchReplacer $urlRewriteBunchReplacer
     * @param DataMapPoolInterface $dataMapPoolInterface
     */
    public function __construct(
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlRewriteHandler $urlRewriteHandler,
        UrlRewriteBunchReplacer $urlRewriteBunchReplacer,
        DataMapPoolInterface $dataMapPoolInterface
    ) {
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlRewriteHandler = $urlRewriteHandler;
        $this->urlRewriteBunchReplacer = $urlRewriteBunchReplacer;
        $this->dataMapPoolInterface = $dataMapPoolInterface;
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
            $categoryUrlRewriteResult = $this->categoryUrlRewriteGenerator->generate($category);
            $this->urlRewriteBunchReplacer->doBunchReplace($categoryUrlRewriteResult);

            $productUrlRewriteResult = $this->urlRewriteHandler->generateProductUrlRewrites($category);
            $this->urlRewriteBunchReplacer->doBunchReplace($productUrlRewriteResult);

            $this->dataMapPoolInterface->resetDataMap(DataCategoryUrlRewriteMap::class, $category->getEntityId());
            $this->dataMapPoolInterface->resetDataMap(DataProductUrlRewriteMap::class, $category->getEntityId());
        }
    }
}
