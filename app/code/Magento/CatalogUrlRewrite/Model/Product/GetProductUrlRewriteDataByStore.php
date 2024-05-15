<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Product\GetUrlRewriteData;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\Store;

/**
 * Product data needed for url rewrite generation locator class
 */
class GetProductUrlRewriteDataByStore implements ResetAfterRequestInterface
{
    /**
     * @var array
     */
    private $urlRewriteData = [];

    /**
     * @var GetUrlRewriteData
     */
    private $getUrlRewriteData;

    /**
     * @param GetUrlRewriteData $getUrlRewriteData
     */
    public function __construct(GetUrlRewriteData $getUrlRewriteData)
    {
        $this->getUrlRewriteData = $getUrlRewriteData;
    }

    /**
     * Retrieves data for product by store
     *
     * @param ProductInterface $product
     * @param int $storeId
     * @return array
     */
    public function execute(ProductInterface $product, int $storeId): array
    {
        $productId = $product->getId();
        if (isset($this->urlRewriteData[$productId][$storeId])) {
            return $this->urlRewriteData[$productId][$storeId];
        }
        if (empty($this->urlRewriteData[$productId])) {
            $storesData = $this->getUrlRewriteData->execute($product);
            foreach ($storesData as $storeData) {
                $this->urlRewriteData[$productId][$storeData['store_id']] = [
                    'visibility' =>
                        (int)($storeData['visibility'] ?? $storesData[Store::DEFAULT_STORE_ID]['visibility']),
                    'url_key' =>
                        $storeData['url_key'] ?? $storesData[Store::DEFAULT_STORE_ID]['url_key'],
                ];
            }
        }

        if (!isset($this->urlRewriteData[$productId][$storeId])) {
            $this->urlRewriteData[$productId][$storeId] = $this->urlRewriteData[$productId][Store::DEFAULT_STORE_ID];
        }

        return $this->urlRewriteData[$productId][$storeId];
    }

    /**
     * Clears product url rewrite data in local cache
     *
     * @param ProductInterface $product
     */
    public function clearProductUrlRewriteDataCache(ProductInterface $product)
    {
        unset($this->urlRewriteData[$product->getId()]);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->urlRewriteData = [];
    }
}
