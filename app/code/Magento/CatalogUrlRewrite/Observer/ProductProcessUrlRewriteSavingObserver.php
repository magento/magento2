<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogUrlRewrite\Model\Products\AppendUrlRewritesToProducts;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreResolver\GetStoresListByWebsiteIds;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class ProductProcessUrlRewriteSavingObserver
 *
 * Generates urls for product url rewrites
 */
class ProductProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var AppendUrlRewritesToProducts
     */
    private $appendRewrites;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GetStoresListByWebsiteIds
     */
    private $getStoresList;

    /**
     * @var StoreViewService
     */
    private $storeViewService;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param AppendUrlRewritesToProducts $appendRewrites
     * @param ScopeConfigInterface $scopeConfig
     * @param GetStoresListByWebsiteIds $getStoresList
     * @param StoreViewService $storeViewService
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        AppendUrlRewritesToProducts $appendRewrites,
        ScopeConfigInterface $scopeConfig,
        GetStoresListByWebsiteIds $getStoresList,
        StoreViewService $storeViewService
    ) {
        $this->urlPersist = $urlPersist;
        $this->appendRewrites = $appendRewrites;
        $this->scopeConfig = $scopeConfig;
        $this->getStoresList = $getStoresList;
        $this->storeViewService = $storeViewService;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param Observer $observer
     * @return void
     * @throws UrlAlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        if ($this->isNeedUpdateRewrites($product)) {
            $this->deleteObsoleteRewrites($product);
            $oldWebsiteIds = $product->getOrigData('website_ids') ?? [];
            $storesToAdd = $this->getStoresList->execute(
                array_diff($product->getWebsiteIds(), $oldWebsiteIds)
            );

            if ($product->getStoreId() === Store::DEFAULT_STORE_ID
                && $product->dataHasChangedFor('visibility')
                && (int) $product->getOrigData('visibility') === Visibility::VISIBILITY_NOT_VISIBLE
            ) {
                foreach ($product->getStoreIds() as $storeId) {
                    if (!$this->storeViewService->doesEntityHaveOverriddenVisibilityForStore(
                        $storeId,
                        $product->getId(),
                        Product::ENTITY
                    )
                    ) {
                        $storesToAdd[] = $storeId;
                    }
                }
                $storesToAdd = array_unique($storesToAdd);
            }
            $this->appendRewrites->execute([$product], $storesToAdd);
        }
    }

    /**
     * Remove obsolete Url rewrites
     *
     * @param Product $product
     */
    private function deleteObsoleteRewrites(Product $product): void
    {
        //do not perform redundant delete request for new product
        if ($product->getOrigData('entity_id') === null) {
            return;
        }
        $oldWebsiteIds = $product->getOrigData('website_ids') ?? [];
        $storesToRemove = $this->getStoresList->execute(
            array_diff($oldWebsiteIds, $product->getWebsiteIds())
        );
        if ((int)$product->getVisibility() === Visibility::VISIBILITY_NOT_VISIBLE) {
            if ($product->getStoreId() === Store::DEFAULT_STORE_ID) {
                foreach ($product->getStoreIds() as $storeId) {
                    if (!$this->storeViewService->doesEntityHaveOverriddenVisibilityForStore(
                        $storeId,
                        $product->getId(),
                        Product::ENTITY
                    )
                    ) {
                        $storesToRemove[] = $storeId;
                    }
                }
            } else {
                $storesToRemove[] = $product->getStoreId();
            }
            $storesToRemove = array_unique($storesToRemove);
        }
        $storesToRemove = array_filter($storesToRemove);
        if ($storesToRemove) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $storesToRemove,
                ]
            );
        }
    }

    /**
     * Is website assignment updated
     *
     * @param Product $product
     * @return bool
     */
    private function isWebsiteChanged(Product $product)
    {
        $oldWebsiteIds = $product->getOrigData('website_ids');
        $newWebsiteIds = $product->getWebsiteIds();

        return array_diff($oldWebsiteIds, $newWebsiteIds) || array_diff($newWebsiteIds, $oldWebsiteIds);
    }

    /**
     * Is product rewrites need to be updated
     *
     * @param Product $product
     * @return bool
     */
    private function isNeedUpdateRewrites(Product $product): bool
    {
        return ($product->dataHasChangedFor('url_key')
                && (int)$product->getVisibility() !== Visibility::VISIBILITY_NOT_VISIBLE)
            || ($product->getIsChangedCategories() && $this->isGenerateCategoryProductRewritesEnabled())
            || $this->isWebsiteChanged($product)
            || $product->dataHasChangedFor('visibility');
    }

    /**
     * Return product use category path in rewrite config value
     *
     * @return bool
     */
    private function isGenerateCategoryProductRewritesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('catalog/seo/generate_category_product_rewrites');
    }
}
