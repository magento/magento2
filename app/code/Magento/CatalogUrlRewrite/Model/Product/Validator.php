<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Url Rewrites Product validator.
 */
class Validator
{
    /**
     * @var ProductUrlPathGenerator
     */
    private $productUrlPathGenerator;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlFinderInterface $urlFinder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductUrlPathGenerator $productUrlPathGenerator,
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager
    ) {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlFinder = $urlFinder;
        $this->storeManager = $storeManager;
    }

    /**
     * Find Url Key conflicts of a product.
     *
     * @param Product $product
     * @return array Array of conflicting Url Keys.
     */
    public function findUrlKeyConflicts(Product $product): array
    {
        if (!$product->getUrlKey()) {
            $urlKey = $this->productUrlPathGenerator->getUrlKey($product);
            $product->setUrlKey($urlKey);
        }

        $stores = $this->storeManager->getStores();

        $storeIdsToPathForSave = [];
        $searchData = [
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::REQUEST_PATH => []
        ];

        foreach ($stores as $store) {
            if (!in_array($store->getWebsiteId(), $product->getWebsiteIds())) {
                continue;
            }

            $urlPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $store->getId());
            $storeIdsToPathForSave[$store->getId()] = $urlPath;
            $searchData[UrlRewrite::REQUEST_PATH][] = $urlPath;
        }

        $urlRewrites = $this->urlFinder->findAllByData($searchData);
        $conflicts = [];

        foreach ($urlRewrites as $urlRewrite) {
            if (in_array($urlRewrite->getRequestPath(), $storeIdsToPathForSave)
                && isset($storeIdsToPathForSave[$urlRewrite->getStoreId()])
                && $storeIdsToPathForSave[$urlRewrite->getStoreId()] == $urlRewrite->getRequestPath()
                && $product->getId() != $urlRewrite->getEntityId()) {
                $conflicts[] = $urlRewrite;
            }
        }

        return $conflicts;
    }
}
