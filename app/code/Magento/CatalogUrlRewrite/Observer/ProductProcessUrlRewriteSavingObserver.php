<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\ObjectManager;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Store\Api\StoreWebsiteRelationInterface;

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
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlPathGenerator|null $productUrlPathGenerator
     * @param StoreManagerInterface|null $storeManager
     * @param StoreWebsiteRelationInterface|null $storeWebsiteRelation
     */
    public function __construct(
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductUrlPathGenerator $productUrlPathGenerator = null,
        StoreManagerInterface $storeManager = null,
        StoreWebsiteRelationInterface $storeWebsiteRelation = null
    ) {
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productUrlPathGenerator = $productUrlPathGenerator ?: ObjectManager::getInstance()
            ->get(ProductUrlPathGenerator::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
        $this->storeWebsiteRelation = $storeWebsiteRelation ?: ObjectManager::getInstance()
            ->get(StoreWebsiteRelationInterface::class);
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        if ($product->dataHasChangedFor('url_key')
            || $product->getIsChangedCategories()
            || $product->getIsChangedWebsites()
            || $product->dataHasChangedFor('visibility')
        ) {
            if ($product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE) {
                $product->unsUrlPath();
                $product->setUrlPath($this->productUrlPathGenerator->getUrlPath($product));
                $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));

                //Remove any rewrite URLs for websites the product is not in
                foreach ($this->storeManager->getWebsites() as $website) {
                    $websiteId = $website->getWebsiteId();
                    if (!in_array($websiteId, $product->getWebsiteIds())) {
                        foreach ($this->storeWebsiteRelation->getStoreByWebsiteId($websiteId) as $storeId) {
                            $this->urlPersist->deleteByData([
                                UrlRewrite::ENTITY_ID => $product->getId(),
                                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                                UrlRewrite::STORE_ID => $storeId
                            ]);
                        }
                    }
                }
            }
        }
    }
}
