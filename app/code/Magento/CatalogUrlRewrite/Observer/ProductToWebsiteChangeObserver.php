<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class ProductToWebsiteChangeObserver
 *
 * Observer to update the Rewrite URLs for a product.
 * This observer is triggered by the product_action_attribute.website.update
 * consumer in response to Mass Action changes in the Admin Product Grid.
 */
class ProductToWebsiteChangeObserver implements ObserverInterface
{
    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     *
     *  * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        StoreWebsiteRelationInterface $storeWebsiteRelation = null
    ) {
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productRepository = $productRepository;
        $this->storeWebsiteRelation = $storeWebsiteRelation ?:
            ObjectManager::getInstance()->get(StoreWebsiteRelationInterface::class);
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        foreach ($observer->getEvent()->getProducts() as $productId) {
            /* @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById(
                $productId,
                false,
                Store::DEFAULT_STORE_ID,
                true
            );

            // Remove the URLs from websites this product no longer belongs to
            if ($observer->getEvent()->getActionType() == "remove" && $observer->getEvent()->getWebsiteIds()) {
                foreach ($observer->getEvent()->getWebsiteIds() as $webId) {
                    foreach ($this->storeWebsiteRelation->getStoreByWebsiteId($webId) as $storeId) {
                        $this->urlPersist->deleteByData([
                            UrlRewrite::ENTITY_ID => $product->getId(),
                            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                            UrlRewrite::STORE_ID => $storeId
                        ]);
                    }
                }
            }

            // Refresh all existing URLs for the product
            if (!empty($this->productUrlRewriteGenerator->generate($product))) {
                if ($product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE) {
                    $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));
                }
            }
        }
    }
}
