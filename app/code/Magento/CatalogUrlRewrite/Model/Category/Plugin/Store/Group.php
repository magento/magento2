<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Store;

use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\Store;
use Magento\Framework\Model\AbstractModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Group
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     * @since 2.0.0
     */
    protected $urlPersist;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     * @since 2.0.0
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     * @since 2.0.0
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator
     * @since 2.0.0
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param CategoryFactory $categoryFactory
     * @param ProductFactory $productFactory
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        StoreManagerInterface $storeManager
    ) {
        $this->urlPersist = $urlPersist;
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->storeManager = $storeManager;
    }

    /**
     * Perform updating url for categories and products assigned to the group
     *
     * @param \Magento\Store\Model\ResourceModel\Group $subject
     * @param \Magento\Store\Model\ResourceModel\Group $result
     * @param AbstractModel $group
     * @return \Magento\Store\Model\ResourceModel\Group
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterSave(
        \Magento\Store\Model\ResourceModel\Group $subject,
        \Magento\Store\Model\ResourceModel\Group $result,
        AbstractModel $group
    ) {
        if (!$group->isObjectNew()
            && ($group->dataHasChangedFor('website_id')
                || $group->dataHasChangedFor('root_category_id'))
        ) {
            $this->storeManager->reinitStores();
            foreach ($group->getStoreIds() as $storeId) {
                $this->urlPersist->deleteByData([UrlRewrite::STORE_ID => $storeId]);
            }

            $this->urlPersist->replace(
                $this->generateCategoryUrls($group->getRootCategoryId(), $group->getStoreIds())
            );

            $this->urlPersist->replace(
                $this->generateProductUrls($group->getWebsiteId(), $group->getOrigData('website_id'))
            );
        }

        return $result;
    }

    /**
     * Generate url rewrites for products assigned to website
     *
     * @param int $websiteId
     * @param int $originWebsiteId
     * @return array
     * @since 2.0.0
     */
    protected function generateProductUrls($websiteId, $originWebsiteId)
    {
        $urls = [];
        $websiteIds = $websiteId != $originWebsiteId
            ? [$websiteId, $originWebsiteId]
            : [$websiteId];
        $collection = $this->productFactory->create()
            ->getCollection()
            ->addCategoryIds()
            ->addAttributeToSelect(['name', 'url_path', 'url_key', 'visibility'])
            ->addWebsiteFilter($websiteIds);
        foreach ($collection as $product) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product->setStoreId(Store::DEFAULT_STORE_ID);
            $urls = array_merge(
                $urls,
                $this->productUrlRewriteGenerator->generate($product)
            );
        }

        return $urls;
    }

    /**
     * @param int $rootCategoryId
     * @param array $storeIds
     * @return array
     * @since 2.0.0
     */
    protected function generateCategoryUrls($rootCategoryId, $storeIds)
    {
        $urls = [];
        $categories = $this->categoryFactory->create()->getCategories($rootCategoryId, 1, false, true);
        foreach ($categories as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category->setStoreId(Store::DEFAULT_STORE_ID);
            $category->setStoreIds($storeIds);
            $urls = array_merge(
                $urls,
                $this->categoryUrlRewriteGenerator->generate($category)
            );
        }
        return $urls;
    }
}
