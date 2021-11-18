<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
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
     * Validate Url Key of a Product has no conflicts.
     *
     * @param Product $product
     * @throws UrlAlreadyExistsException
     */
    public function validateUrlKeyConflicts(Product $product): void
    {
        $stores = $this->storeManager->getStores();

        $storeIdsToPathForSave = [];
        $searchData = [
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::REQUEST_PATH => [],
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
        $exceptionData = [];

        foreach ($urlRewrites as $urlRewrite) {
            if (in_array($urlRewrite->getRequestPath(), $storeIdsToPathForSave)
                && isset($storeIdsToPathForSave[$urlRewrite->getStoreId()])
                && $storeIdsToPathForSave[$urlRewrite->getStoreId()] === $urlRewrite->getRequestPath()
                && $product->getId() !== $urlRewrite->getEntityId()
            ) {
                $exceptionData[$urlRewrite->getUrlRewriteId()] = $urlRewrite->toArray();
            }
        }

        if ($exceptionData) {
            throw new UrlAlreadyExistsException(
                __('URL key for specified store already exists.'),
                null,
                0,
                $exceptionData
            );
        }
    }
}
