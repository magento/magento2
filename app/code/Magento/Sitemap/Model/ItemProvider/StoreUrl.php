<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ItemProvider;

use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

/**
 * Class for adding Store Url in sitemap
 */
class StoreUrl implements ItemProviderInterface
{
    /**
     * StoreUrlSitemapItemResolver constructor.
     *
     * @param ConfigReaderInterface $configReader Config reader
     * @param SitemapItemInterfaceFactory $itemFactory Sitemap item factory
     */
    public function __construct(
        private readonly ConfigReaderInterface $configReader,
        private readonly SitemapItemInterfaceFactory $itemFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getItems($storeId)
    {
        $items[] = $this->itemFactory->create([
            'url' => '',
            'priority' => $this->configReader->getPriority($storeId),
            'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
        ]);

        return $items;
    }
}
