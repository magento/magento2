<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use Magento\Store\Model\ScopeInterface;

class ProductSitemapItemResolver implements SitemapItemResolverInterface
{
    /**#@+
     * Xpath config settings
     */
    const XML_PATH_PRODUCT_CHANGEFREQ = 'sitemap/product/changefreq';
    const XML_PATH_PRODUCT_PRIORITY = 'sitemap/product/priority';
    /**#@-*/

    /**
     * Product factory
     *
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * Sitemap item factory
     *
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * Scope config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ProductSitemapItemResolver constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductFactory $productFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductFactory $productFactory,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productFactory = $productFactory;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->productFactory->create()->getCollection($storeId);
        $items = array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'updatedAt' => $item->getUpdatedAt(),
                'images' => $item->getImages(),
                'priority' => $this->getProductPriority($storeId),
                'changeFrequency' => $this->getProductChangeFrequency($storeId),
            ]);
        }, $collection);

        return $items;
    }

    /**
     * Get page priority
     *
     * @param int $storeId
     * @return string
     */
    private function getProductPriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get page change frequency
     *
     * @param int $storeId
     * @return string
     */
    private function getProductChangeFrequency($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
