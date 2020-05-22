<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Store;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Plugin which is listening store resource model and on save or on delete replace catalog url rewrites
 *
 * @see \Magento\Store\Model\ResourceModel\Store
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View
{
    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CategoryUrlRewriteGenerator
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var AbstractModel
     */
    private $origStore;

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
     * Set original store before saving
     *
     * @param Store $object
     * @param AbstractModel $store
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        Store $object,
        AbstractModel $store
    ): void {
        $this->origStore = $store;
    }

    /**
     * Regenerate urls on store after save
     *
     * @param Store $object
     * @param Store $store
     * @return Store
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        Store $object,
        Store $store
    ): Store {
        if ($this->origStore->isObjectNew() || $this->origStore->dataHasChangedFor('group_id')) {
            if (!$this->origStore->isObjectNew()) {
                $this->urlPersist->deleteByData([UrlRewrite::STORE_ID => $this->origStore->getId()]);
            }

            $this->urlPersist->replace(
                $this->generateCategoryUrls((int)$this->origStore->getRootCategoryId(), (int)$this->origStore->getId())
            );

            $this->urlPersist->replace(
                $this->generateProductUrls((int)$this->origStore->getId())
            );
        }

        return $store;
    }

    /**
     * Generate url rewrites for products assigned to store
     *
     * @param int $storeId
     * @return array
     */
    protected function generateProductUrls(int $storeId): array
    {
        $urls = [];
        $rewrites = [];
        $collection = $this->productFactory->create()
            ->getCollection()
            ->addCategoryIds()
            ->addAttributeToSelect(['name', 'url_path', 'url_key', 'visibility'])
            ->addStoreFilter($storeId);
        foreach ($collection as $product) {
            $product->setStoreId($storeId);
            /** @var Product $product */
            $rewrites[] = $this->productUrlRewriteGenerator->generate($product);
        }
        $urls = array_merge($urls, ...$rewrites);

        return $urls;
    }

    /**
     * Generate url rewrites for categories assigned to store
     *
     * @param int $rootCategoryId
     * @param int $storeId
     * @return array
     */
    protected function generateCategoryUrls(int $rootCategoryId, int $storeId): array
    {
        $urls = [];
        $rewrites = [];
        $categories = $this->categoryFactory->create()->getCategories($rootCategoryId, 1, false, true);
        foreach ($categories as $category) {
            /** @var Category $category */
            $category->setStoreId($storeId);
            $rewrites[] = $this->categoryUrlRewriteGenerator->generate($category);
        }
        $urls = array_merge($urls, ...$rewrites);

        return $urls;
    }

    /**
     * Delete unused url rewrites
     *
     * @param Store $subject
     * @param Store $result
     * @param AbstractModel $store
     * @return Store
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        Store $subject,
        Store $result,
        AbstractModel $store
    ): Store {
        $this->urlPersist->deleteByData([UrlRewrite::STORE_ID => $store->getId()]);

        return $result;
    }
}
