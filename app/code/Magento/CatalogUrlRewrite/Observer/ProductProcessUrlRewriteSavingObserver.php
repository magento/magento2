<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Event\Observer;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\Storage\DeleteEntitiesFromStores;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductProcessUrlRewriteSavingObserver
 *
 * Observer to update the Rewrite URLs for a product.
 * This observer is triggered on the save function when making changes
 * to the products website on the Product Edit page.
 */
class ProductProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /**
     * @var ProductUrlRewriteGenerator
     */
    private $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductScopeRewriteGenerator
     */
    private $productScopeRewriteGenerator;

    /**
     * @var DeleteEntitiesFromStores
     */
    private $deleteEntitiesFromStores;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param StoreManagerInterface $storeManager
     * @param ProductScopeRewriteGenerator $productScopeRewriteGenerator
     * @param DeleteEntitiesFromStores $deleteEntitiesFromStores
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductUrlPathGenerator $productUrlPathGenerator,
        StoreManagerInterface $storeManager,
        ProductScopeRewriteGenerator $productScopeRewriteGenerator,
        DeleteEntitiesFromStores $deleteEntitiesFromStores,
        CollectionFactory $collectionFactory
    ) {
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->storeManager = $storeManager;
        $this->productScopeRewriteGenerator = $productScopeRewriteGenerator;
        $this->deleteEntitiesFromStores = $deleteEntitiesFromStores;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param Observer $observer
     * @return void
     * @throws UrlAlreadyExistsException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        if ($product->dataHasChangedFor('url_key')
            || $product->getIsChangedCategories()
            || $product->getIsChangedWebsites()
            || $product->dataHasChangedFor('visibility')
        ) {
            //Refresh rewrite urls
            $product->unsUrlPath();
            $product->setUrlPath($this->productUrlPathGenerator->getUrlPath($product));
            if (!empty($this->productUrlRewriteGenerator->generate($product))) {
                $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));
            }

            $storeIdsToRemove = [];
            $productWebsiteMap = array_flip($product->getWebsiteIds());
            $storeVisibilities = $this->collectionFactory->create()
                    ->getAllAttributeValues(ProductInterface::VISIBILITY);
            if ($this->productScopeRewriteGenerator->isGlobalScope($product->getStoreId())) {
                //Remove any rewrite URLs for websites the product is not in, or is not visible in. Global Scope.
                foreach ($this->storeManager->getStores() as $store) {
                    $websiteId = $store->getWebsiteId();
                    $storeId = $store->getStoreId();
                    if (!isset($productWebsiteMap[$websiteId])) {
                        $storeIdsToRemove[] = $storeId;
                        continue;
                    }
                    //Check the visibility of the product in each store.
                    if (isset($storeVisibilities[$product->getId()][$storeId])
                        && ($storeVisibilities[$product->getId()][$storeId] === Visibility::VISIBILITY_NOT_VISIBLE)) {
                        $storeIdsToRemove[] = $storeId;
                    }
                }
            } else {
                //Only remove rewrite for current scope
                $websiteId = $product->getStore()->getWebsiteId();
                $storeId = $product->getStoreId();
                if (!isset($productWebsiteMap[$websiteId]) ||
                    (isset($storeVisibilities[$product->getId()][$storeId])
                        && ($storeVisibilities[$product->getId()][$storeId] === Visibility::VISIBILITY_NOT_VISIBLE))) {
                    $storeIdsToRemove[] = $storeId;
                }
            }
            if (count($storeIdsToRemove)) {
                $this->deleteEntitiesFromStores->execute(
                    $storeIdsToRemove,
                    [$product->getId()],
                    ProductUrlRewriteGenerator::ENTITY_TYPE
                );
            }
        }
    }
}
