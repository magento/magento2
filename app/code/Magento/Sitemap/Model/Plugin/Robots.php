<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\Plugin;

use Magento\Sitemap\Helper\Data as SitemapHelper;
use Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Store\Model\StoreResolver;

/**
 * Plug-in for getData() method of \Magento\Robots\Model\Data class.
 * Adds link for current sitemap file to robots.txt data.
 */
class Robots
{
    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @var CollectionFactory
     */
    private $sitemapCollectionFactory;

    /**
     * @var SitemapHelper
     */
    private $sitemapHelper;

    /**
     * @param StoreResolver $storeResolver
     * @param CollectionFactory $sitemapCollectionFactory
     * @param SitemapHelper $sitemapHelper
     */
    public function __construct(
        StoreResolver $storeResolver,
        CollectionFactory $sitemapCollectionFactory,
        SitemapHelper $sitemapHelper
    ) {
        $this->storeResolver = $storeResolver;
        $this->sitemapCollectionFactory = $sitemapCollectionFactory;
        $this->sitemapHelper = $sitemapHelper;
    }

    /**
     * Add link for sitemap file into robots.txt data
     *
     * Detects if sitemap file information is required to be added to robots.txt data,
     * then gets the name of sitemap file that linked with current store,
     * and adds record for this sitemap file into result data.
     *
     * @param \Magento\Robots\Model\Data $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(\Magento\Robots\Model\Data $subject, $result)
    {
        $storeId = $this->storeResolver->getCurrentStoreId();

        if ((bool)$this->sitemapHelper->getEnableSubmissionRobots($storeId)) {
            /** @var \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection $collection */
            $collection = $this->sitemapCollectionFactory->create();
            $collection->addStoreFilter([$storeId]);

            foreach ($collection as $sitemap) {
                /** @var \Magento\Sitemap\Model\Sitemap $sitemap */
                $sitemapFilename = $sitemap->getSitemapFilename();
                $sitemapPath = $sitemap->getSitemapPath();

                $robotsSitemapLine = 'Sitemap: ' . $sitemap->getSitemapUrl($sitemapPath, $sitemapFilename);

                if (strpos($result, $robotsSitemapLine) === false) {
                    if (!empty($result)) {
                        $result .= $this->getEndOfLine($result);
                    }
                    $result .= $robotsSitemapLine;
                }
            }
        }

        return $result;
    }

    /**
     * Detects and returns 'end of line' symbol that currently used in text argument
     *
     * @param string $text
     * @return string
     */
    private function getEndOfLine($text)
    {
        foreach (["\r\n", "\r", "\n"] as $eol) {
            if (strpos($text, $eol) !== false) {
                return $eol;
            }
        }
        return PHP_EOL;
    }
}
