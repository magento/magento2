<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Block\UrlKeyRenderer;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;

class CategoryProcessUrlRewriteMovingObserver implements ObserverInterface
{
    /** @var CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var UrlRewriteHandler */
    protected $urlRewriteHandler;

    /**
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlRewriteHandler $urlRewriteHandler
     */
    public function __construct(
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ScopeConfigInterface $scopeConfig,
        UrlRewriteHandler $urlRewriteHandler
    ) {
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->scopeConfig = $scopeConfig;
        $this->urlRewriteHandler = $urlRewriteHandler;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        if ($category->dataHasChangedFor('parent_id')) {
            $saveRewritesHistory = $this->scopeConfig->isSetFlag(
                UrlKeyRenderer::XML_PATH_SEO_SAVE_HISTORY,
                ScopeInterface::SCOPE_STORE,
                $category->getStoreId()
            );
            $category->setData('save_rewrites_history', $saveRewritesHistory);
            $urlRewrites = array_merge(
                $this->categoryUrlRewriteGenerator->generate($category, true),
                $this->urlRewriteHandler->generateProductUrlRewrites($category)
            );
            $this->urlRewriteHandler->deleteCategoryRewritesForChildren($category);
            $this->urlPersist->replace($urlRewrites);
        }
    }
}
