<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ItemProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CmsPage
 */
class CmsPage implements ItemProviderInterface
{
    const XML_PATH_HOMEPAGE_IDENTIFIER = 'web/default/cms_home_page';

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
     * Config reader
     *
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * CmsPage constructor.
     *
     * @param ConfigReaderInterface          $configReader
     * @param PageFactory                    $cmsPageFactory
     * @param SitemapItemInterfaceFactory    $itemFactory
     * @param StoreManagerInterface          $storeManager
     * @param ScopeConfigInterface           $scopeConfig
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(
        ConfigReaderInterface $configReader,
        PageFactory $cmsPageFactory,
        SitemapItemInterfaceFactory $itemFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->cmsPageFactory = $cmsPageFactory;
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @inheritdoc
     */
    public function getItems($storeId)
    {
        $collection = $this->cmsPageFactory->create()->getCollection($storeId);
        $items = array_map(
            function ($item) use ($storeId) {
                return $this->itemFactory->create(
                    [
                    'url' => $item->getUrl(),
                    'updatedAt' => $item->getUpdatedAt(),
                    'images' => $item->getImages(),
                    'priority' => $this->configReader->getPriority($storeId),
                    'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
                    ]
                );
            },
            $collection
        );

        $homePageIdentifierValue = $this->scopeConfig->getValue(
            self::XML_PATH_HOMEPAGE_IDENTIFIER,
            ScopeInterface::SCOPE_STORE
        );

        $homepage = $this->pageFactory->create()->load($homePageIdentifierValue, 'identifier');

        /**
         * Add configured homepage to sitemap using base_url as url
         */
        $items[] = $this->itemFactory->create(
            [
            'url' => $this->storeManager->getStore($storeId)->getBaseUrl(),
            'updatedAt' => $homepage->getUpdateTime(),
            'priority' => $this->configReader->getPriority($storeId),
            'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
            ]
        );

        return $items;
    }
}
