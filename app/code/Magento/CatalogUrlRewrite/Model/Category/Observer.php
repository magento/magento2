<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\CatalogUrlRewrite\Block\UrlKeyRenderer;

class Observer
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /** @var CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var array */
    protected $isSkippedProduct;

    /** @var \Magento\Catalog\Model\Resource\Product\CollectionFactory */
    protected $productCollectionFactory;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->scopeConfig = $scopeConfig;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function processUrlRewriteSaving(EventObserver $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        if ($category->getParentId() == Category::TREE_ROOT_ID) {
            return;
        }
        if ($category->dataHasChangedFor('url_key') || $category->getIsChangedProductList()) {
            $urlRewrites = array_merge(
                $this->categoryUrlRewriteGenerator->generate($category),
                $this->generateProductUrlRewrites($category)
            );
            $this->urlPersist->replace($urlRewrites);
        }
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function processUrlRewriteMoving(EventObserver $observer)
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
                $this->categoryUrlRewriteGenerator->generate($category),
                $this->generateProductUrlRewrites($category)
            );
            $this->deleteCategoryRewritesForChildren($category);
            $this->urlPersist->replace($urlRewrites);
        }
    }

    /**
     * Generate url rewrites for products assigned to category
     *
     * @param Category $category
     * @return array
     */
    protected function generateProductUrlRewrites(Category $category)
    {
        $this->isSkippedProduct = [];
        $saveRewriteHistory = $category->getData('save_rewrites_history');
        $storeId = $category->getStoreId();
        $productUrls = [];
        if ($category->getAffectedProductIds()) {
            $this->isSkippedProduct = $category->getAffectedProductIds();
            $collection = $this->productCollectionFactory->create()
                ->setStoreId($storeId)
                ->addIdFilter($category->getAffectedProductIds())
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');
            foreach ($collection as $product) {
                $product->setStoreId($storeId);
                $product->setData('save_rewrites_history', $saveRewriteHistory);
                $productUrls = array_merge($productUrls, $this->productUrlRewriteGenerator->generate($product));
            }
        } else {
            $productUrls = array_merge(
                $productUrls,
                $this->getCategoryProductsUrlRewrites($category, $storeId, $saveRewriteHistory)
            );
        }
        foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
            $productUrls = array_merge(
                $productUrls,
                $this->getCategoryProductsUrlRewrites($childCategory, $storeId, $saveRewriteHistory)
            );
        }
        return $productUrls;
    }

    /**
     * @param Category $category
     * @param int $storeId
     * @param bool $saveRewriteHistory
     * @return UrlRewrite[]
     */
    protected function getCategoryProductsUrlRewrites(Category $category, $storeId, $saveRewriteHistory)
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $productCollection */
        $productCollection = $category->getProductCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');
        $productUrls = [];
        foreach ($productCollection as $product) {
            if (in_array($product->getId(), $this->isSkippedProduct)) {
                continue;
            }
            $this->isSkippedProduct[] = $product->getId();
            $product->setStoreId($storeId);
            $product->setData('save_rewrites_history', $saveRewriteHistory);
            $productUrls = array_merge($productUrls, $this->productUrlRewriteGenerator->generate($product));
        }
        return $productUrls;
    }

    /**
     * @param Category $category
     * @return void
     */
    protected function deleteCategoryRewritesForChildren(Category $category)
    {
        $categoryIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
        $categoryIds[] = $category->getId();
        foreach ($categoryIds as $categoryId) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $categoryId,
                    UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::METADATA => serialize(['category_id' => $categoryId]),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }
    }
}
