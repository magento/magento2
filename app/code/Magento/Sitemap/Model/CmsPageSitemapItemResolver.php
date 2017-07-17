<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;

class CmsPageSitemapItemResolver implements SitemapItemResolverInterface
{

    /**#@+
     * Xpath config settings
     */
    const XML_PATH_PAGE_CHANGEFREQ = 'sitemap/page/changefreq';
    const XML_PATH_PAGE_PRIORITY = 'sitemap/page/priority';

    /**#@-*/

    /**
     * Cms page factory
     *
     * @var PageFactory
     */
    private $cmsPageFactory;

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
     * CmsPageSitemapItemResolver constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param PageFactory $cmsPageFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        PageFactory $cmsPageFactory,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cmsPageFactory = $cmsPageFactory;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->cmsPageFactory->create()->getCollection($storeId);
        var_dump($collection);
        $items = array_map(function($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'updatedAt' => $item->getUpdatedAt(),
                'images' => $item->getImages(),
                'priority' => $this->getPagePriority($storeId),
                'changeFrequency' => $this->getPageChangeFrequency($storeId),
            ]);
        }, $collection);

        var_dump($items);

        return $items;
    }

    /**
     * Get page priority
     *
     * @param int $storeId
     * @return string
     */
    private function getPagePriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PAGE_PRIORITY,
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
    private function getPageChangeFrequency($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_PAGE_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}