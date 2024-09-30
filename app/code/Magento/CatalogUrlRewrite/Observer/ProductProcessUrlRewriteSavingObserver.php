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
     * @param UrlPersistInterface $urlPersist
     * @param AppendUrlRewritesToProducts|null $appendRewrites
     * @param ScopeConfigInterface $scopeConfig
     * @param GetStoresListByWebsiteIds $getStoresList
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        AppendUrlRewritesToProducts $appendRewrites,
        ScopeConfigInterface $scopeConfig,
        GetStoresListByWebsiteIds $getStoresList
    ) {
        $this->urlPersist = $urlPersist;
        $this->appendRewrites = $appendRewrites;
        $this->scopeConfig = $scopeConfig;
        $this->getStoresList = $getStoresList;
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
            $isGlobalScope = $product->getStoreId() == Store::DEFAULT_STORE_ID;
            $storesToRemove[] = $isGlobalScope ? $product->getStoreIds() : $product->getStoreId();
        }
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
