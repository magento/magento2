<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Robots\Model\Config\Value;
use Magento\Sitemap\Helper\Data as SitemapHelper;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;

/**
 * Prepares sitemap links to add to the robots.txt file
 *
 * @api
 * @since 100.2.0
 */
class Robots extends AbstractBlock implements IdentityInterface
{
    /**
     * @var CollectionFactory
     */
    private $sitemapCollectionFactory;

    /**
     * @var SitemapHelper
     */
    private $sitemapHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param StoreResolver $storeResolver
     * @param CollectionFactory $sitemapCollectionFactory
     * @param SitemapHelper $sitemapHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        StoreResolver $storeResolver,
        CollectionFactory $sitemapCollectionFactory,
        SitemapHelper $sitemapHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->sitemapCollectionFactory = $sitemapCollectionFactory;
        $this->sitemapHelper = $sitemapHelper;
        $this->storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    /**
     * Prepare sitemap links to add to the robots.txt file
     *
     * Collects sitemap links for all stores of given website.
     * Detects if sitemap file information is required to be added to robots.txt
     * and adds links for this sitemap files into result data.
     *
     * @return string
     * @since 100.2.0
     */
    protected function _toHtml()
    {
        $defaultStore = $this->storeManager->getDefaultStoreView();

        /** @var \Magento\Store\Model\Website $website */
        $website = $this->storeManager->getWebsite($defaultStore->getWebsiteId());

        $storeIds = [];
        foreach ($website->getStoreIds() as $storeId) {
            if ((bool)$this->sitemapHelper->getEnableSubmissionRobots($storeId)) {
                $storeIds[] = (int)$storeId;
            }
        }

        $links = [];
        if ($storeIds) {
            $links = array_merge($links, $this->getSitemapLinks($storeIds));
        }

        return $links ? implode(PHP_EOL, $links) . PHP_EOL : '';
    }

    /**
     * Retrieve sitemap links for given store
     *
     * Gets the names of sitemap files that linked with given store,
     * and adds links for this sitemap files into result array.
     *
     * @param int[] $storeIds
     * @return array
     * @since 100.2.0
     */
    protected function getSitemapLinks(array $storeIds)
    {
        $sitemapLinks = [];

        /** @var \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection $collection */
        $collection = $this->sitemapCollectionFactory->create();
        $collection->addStoreFilter($storeIds);

        foreach ($collection as $sitemap) {
            /** @var \Magento\Sitemap\Model\Sitemap $sitemap */
            $sitemapFilename = $sitemap->getSitemapFilename();
            $sitemapPath = $sitemap->getSitemapPath();

            $sitemapUrl = $sitemap->getSitemapUrl($sitemapPath, $sitemapFilename);
            $sitemapLinks[$sitemapUrl] = 'Sitemap: ' . $sitemapUrl;
        }

        return $sitemapLinks;
    }

    /**
     * Get unique page cache identities
     *
     * @return array
     * @since 100.2.0
     */
    public function getIdentities()
    {
        return [
            Value::CACHE_TAG . '_' . $this->storeManager->getDefaultStoreView()->getId(),
        ];
    }
}
