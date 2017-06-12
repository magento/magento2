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
use Magento\Store\Model\StoreResolver;

/**
 * Prepares sitemap links to add to the robots.txt file
 *
 * @api
 */
class Robots extends AbstractBlock implements IdentityInterface
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
     * @param Context $context
     * @param StoreResolver $storeResolver
     * @param CollectionFactory $sitemapCollectionFactory
     * @param SitemapHelper $sitemapHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreResolver $storeResolver,
        CollectionFactory $sitemapCollectionFactory,
        SitemapHelper $sitemapHelper,
        array $data = []
    ) {
        $this->storeResolver = $storeResolver;
        $this->sitemapCollectionFactory = $sitemapCollectionFactory;
        $this->sitemapHelper = $sitemapHelper;

        parent::__construct($context, $data);
    }

    /**
     * Prepare sitemap links to add to the robots.txt file
     *
     * Detects if sitemap file information is required to be added to robots.txt,
     * then gets the name of sitemap files that linked with current store,
     * and adds record for this sitemap files into result data.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $result = '';

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
                    $result .= PHP_EOL . $robotsSitemapLine;
                }
            }
        }

        return $result;
    }

    /**
     * Get unique page cache identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [
            Value::CACHE_TAG . '_' . $this->storeResolver->getCurrentStoreId(),
        ];
    }
}
