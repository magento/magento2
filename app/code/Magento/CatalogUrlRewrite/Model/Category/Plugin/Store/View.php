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
namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Store;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Model\AbstractModel;

class View
{
    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var CategoryFactory */
    protected $categoryFactory;

    /** @var ProductFactory */
    protected $productFactory;

    /** @var CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param CategoryFactory $categoryFactory
     * @param ProductFactory $productFactory
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator
    ) {
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @param \Magento\Store\Model\Resource\Store $object
     * @param callable $proceed
     * @param AbstractModel $store
     * @return \Magento\Store\Model\Resource\Store
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Store\Model\Resource\Store $object,
        \Closure $proceed,
        AbstractModel $store
    ) {
        $originStore = $store;
        $result = $proceed($originStore);
        if ($store->isObjectNew() || $store->dataHasChangedFor('group_id')) {
            if (!$store->isObjectNew()) {
                $this->urlPersist->deleteByData([UrlRewrite::STORE_ID => $store->getId()]);
            }

            $this->urlPersist->replace(
                $this->generateCategoryUrls($store->getRootCategoryId(), $store->getId())
            );

            $this->urlPersist->replace(
                $this->generateProductUrls($store->getWebsiteId(), $store->getOrigData('website_id'), $store->getId())
            );
        }

        return $result;
    }

    /**
     * Generate url rewrites for products assigned to website
     *
     * @param int $websiteId
     * @param int $originWebsiteId
     * @param int $storeId
     * @return array
     */
    protected function generateProductUrls($websiteId, $originWebsiteId, $storeId)
    {
        $urls = [];
        $websiteIds = $websiteId != $originWebsiteId && !is_null($originWebsiteId)
            ? [$websiteId, $originWebsiteId]
            : [$websiteId];
        $collection = $this->productFactory->create()
            ->getCollection()
            ->addCategoryIds()
            ->addAttributeToSelect(array('name', 'url_path', 'url_key'))
            ->addWebsiteFilter($websiteIds);
        foreach ($collection as $product) {
            $product->setStoreId($storeId);
            /** @var \Magento\Catalog\Model\Product $product */
            $urls = array_merge(
                $urls,
                $this->productUrlRewriteGenerator->generate($product)
            );
        }
        return $urls;
    }

    /**
     * @param int $rootCategoryId
     * @param int $storeId
     * @return array
     */
    protected function generateCategoryUrls($rootCategoryId, $storeId)
    {
        $urls = [];
        $categories = $this->categoryFactory->create()->getCategories($rootCategoryId, 1, false, true);
        foreach ($categories as $category) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category->setStoreId($storeId);
            $urls = array_merge(
                $urls,
                $this->categoryUrlRewriteGenerator->generate($category)
            );
        }
        return $urls;
    }

    /**
     * @param \Magento\Store\Model\Resource\Store $object
     * @param callable $proceed
     * @param AbstractModel $store
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        \Magento\Store\Model\Resource\Store $object,
        \Closure $proceed,
        AbstractModel $store
    ) {
        $result = $proceed($store);
        $this->urlPersist->deleteByData([UrlRewrite::STORE_ID => $store->getId()]);
        return $result;
    }
}
