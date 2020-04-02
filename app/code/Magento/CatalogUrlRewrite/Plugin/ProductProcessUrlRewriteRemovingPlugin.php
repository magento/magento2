<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\Storage\DeleteEntitiesFromStores;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Class ProductProcessUrlRewriteRemovingPlugin
 *
 * Plugin to update the Rewrite URLs for a product.
 * This plugin is triggered by the product_action_attribute.website.update
 * consumer in response to Mass Action changes in the Admin Product Grid.
 */
class ProductProcessUrlRewriteRemovingPlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var ProductUrlRewriteGenerator
     */
    private $productUrlRewriteGenerator;

    /**
     * @var DeleteEntitiesFromStores
     */
    private $deleteEntitiesFromStores;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param DeleteEntitiesFromStores $deleteEntitiesFromStores
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        UrlPersistInterface $urlPersist,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        DeleteEntitiesFromStores $deleteEntitiesFromStores
    ) {
        $this->productRepository = $productRepository;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->urlPersist = $urlPersist;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->deleteEntitiesFromStores = $deleteEntitiesFromStores;
    }

    /**
     * Function afterUpdateWebsites
     *
     * @param Action $subject
     * @param void $result
     * @param array $productIds
     * @param array $websiteIds
     * @param string $type
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterUpdateWebsites(
        Action $subject,
        $result,
        $productIds,
        $websiteIds,
        $type
    ) {
        foreach ($productIds as $productId) {
            /* @var Product $product */
            $product = $this->productRepository->getById(
                $productId,
                false,
                Store::DEFAULT_STORE_ID,
                true
            );

            // Refresh all existing URLs for the product
            if (!empty($this->productUrlRewriteGenerator->generate($product))) {
                if ($product->isVisibleInSiteVisibility()) {
                    $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));
                }
            }
        }

        $storeIdsToRemove = [];
        // Remove the URLs for products in $productIds array
        // from all stores that belong to websites in $websiteIds array
        if ($type === "remove" && $websiteIds && $productIds) {
            foreach ($websiteIds as $webId) {
                foreach ($this->storeWebsiteRelation->getStoreByWebsiteId($webId) as $storeid) {
                    $storeIdsToRemove[] = $storeid;
                }
            }
            if (count($storeIdsToRemove)) {
                $this->deleteEntitiesFromStores->execute(
                    $storeIdsToRemove,
                    $productIds,
                    ProductUrlRewriteGenerator::ENTITY_TYPE
                );
            }
        }
    }
}
