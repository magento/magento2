<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ItemProvider;

use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

class StoreUrl implements ItemProviderInterface
{
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
     * CategorySitemapItemResolver constructor.
     *
     * @param ConfigReaderInterface $configReader
     * @param SitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ConfigReaderInterface $configReader,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
    }

    /**
     * {@inheritdoc}
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
