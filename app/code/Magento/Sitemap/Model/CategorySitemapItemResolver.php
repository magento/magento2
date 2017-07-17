<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Store\Model\ScopeInterface;

class CategorySitemapItemResolver implements SitemapItemResolverInterface
{
    /**#@+
     * Xpath config settings
     */
    const XML_PATH_CATEGORY_CHANGEFREQ = 'sitemap/category/changefreq';
    const XML_PATH_CATEGORY_PRIORITY = 'sitemap/category/priority';

    /**#@-*/

    /**
     * Category factory
     *
     * @var CategoryFactory
     */
    private $categoryFactory;

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
     * CategorySitemapItemResolver constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryFactory $categoryFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CategoryFactory $categoryFactory,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->itemFactory = $itemFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->categoryFactory->create()->getCollection($storeId);
        $items = array_map(function($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'updatedAt' => $item->getUpdatedAt(),
                'images' => $item->getImages(),
                'priority' => $this->getCategoryPriority($storeId),
                'changeFrequency' => $this->getCategoryChangeFrequency($storeId),
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
    private function getCategoryPriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_PRIORITY,
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
    private function getCategoryChangeFrequency($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}