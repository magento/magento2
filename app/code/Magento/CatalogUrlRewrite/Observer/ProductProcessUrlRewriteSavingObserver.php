<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\UrlRewrite\Model\Storage\DbStorage;
use Magento\Store\Model\Store;

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
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @var ProductRepository $productRepository
     */
    private $productRepository;

    /**
     * @var ProductScopeRewriteGenerator
     */
    private $productScopeRewriteGenerator;

    /**
     * @var DbStorage
     */
    private $dbStorage;

    /**
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param StoreManagerInterface $storeManager
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param ProductRepository $productRepository
     * @param ProductScopeRewriteGenerator $productScopeRewriteGenerator
     * @param DbStorage $dbStorage
     */
    public function __construct(
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductUrlPathGenerator $productUrlPathGenerator,
        StoreManagerInterface $storeManager,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        ProductRepository $productRepository,
        ProductScopeRewriteGenerator $productScopeRewriteGenerator,
        DbStorage $dbStorage
    ) {
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->storeManager = $storeManager;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->productRepository = $productRepository;
        $this->productScopeRewriteGenerator = $productScopeRewriteGenerator;
        $this->dbStorage = $dbStorage;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param Observer $observer
     * @return void
     * @throws UrlAlreadyExistsException
     * @throws NoSuchEntityException
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
            if ($this->productScopeRewriteGenerator->isGlobalScope($product->getStoreId())) {
                //Remove any rewrite URLs for websites the product is not in, or is not visible in. Global Scope.
                foreach ($this->storeManager->getWebsites() as $website) {
                    $websiteId = $website->getWebsiteId();
                    foreach ($this->storeWebsiteRelation->getStoreByWebsiteId($websiteId) as $storeid) {
                        //Load the product for the store we are processing so we can see if it is visible
                        $storeProduct = $this->productRepository->getById(
                            $product->getId(),
                            false,
                            $storeid,
                            true
                        );
                        if (!$storeProduct->isVisibleInSiteVisibility() ||
                            !in_array($websiteId, $product->getWebsiteIds())) {
                            $storeIdsToRemove[] = $storeid;
                        };
                    }
                }
            } else {
                //Only remove rewrite for current scope
                if (!$product->isVisibleInSiteVisibility() ||
                    !in_array($product->getStoreId(), $product->getStoreIds())) {
                    $storeIdsToRemove[] = $product->getStoreId();
                }
            }
            if (count($storeIdsToRemove)) {
                $this->dbStorage->deleteEntitiesFromStores(
                    $storeIdsToRemove,
                    [$product->getId()],
                    ProductUrlRewriteGenerator::ENTITY_TYPE
                );
            }
        }
    }
}
