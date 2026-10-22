<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Model\Product\Copier;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Copier\UrlKeyAvailability;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Rejects a product duplicate URL key whose request path is already taken by any URL rewrite in the affected stores.
 */
class CheckUrlRewritesForUrlKey
{
    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        private readonly ProductUrlPathGenerator $productUrlPathGenerator,
        private readonly UrlFinderInterface $urlFinder
    ) {
    }

    /**
     * Check the duplicate's request path against existing URL rewrites
     *
     * @param UrlKeyAvailability $subject
     * @param bool $result
     * @param Product $duplicate
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAvailable(UrlKeyAvailability $subject, bool $result, Product $duplicate): bool
    {
        if (!$result) {
            return false;
        }

        $requestPaths = $this->getRequestPaths($duplicate);
        if (!$requestPaths) {
            return true;
        }

        $urlRewrites = $this->urlFinder->findAllByData(
            [
                UrlRewrite::REQUEST_PATH => array_values(array_unique($requestPaths)),
                UrlRewrite::STORE_ID => array_keys($requestPaths),
            ]
        );
        foreach ($urlRewrites as $urlRewrite) {
            if (($requestPaths[(int)$urlRewrite->getStoreId()] ?? null) === $urlRewrite->getRequestPath()
                && !$this->isOwnRewrite($urlRewrite, $duplicate)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the duplicate's request path for every store its URL key applies to, keyed by store ID
     *
     * @param Product $duplicate
     * @return string[]
     */
    private function getRequestPaths(Product $duplicate): array
    {
        $storeId = (int)$duplicate->getStoreId();
        $storeIds = $storeId === Store::DEFAULT_STORE_ID
            ? array_map('intval', (array)$duplicate->getStoreIds())
            : [$storeId];
        $requestPaths = [];
        foreach ($storeIds as $id) {
            $requestPaths[$id] = $this->productUrlPathGenerator->getUrlPathWithSuffix($duplicate, $id);
        }

        return $requestPaths;
    }

    /**
     * Check whether the URL rewrite already belongs to the duplicate
     *
     * @param UrlRewrite $urlRewrite
     * @param Product $duplicate
     * @return bool
     */
    private function isOwnRewrite(UrlRewrite $urlRewrite, Product $duplicate): bool
    {
        return $duplicate->getId()
            && $urlRewrite->getEntityType() === ProductUrlRewriteGenerator::ENTITY_TYPE
            && (int)$urlRewrite->getEntityId() === (int)$duplicate->getId();
    }
}
