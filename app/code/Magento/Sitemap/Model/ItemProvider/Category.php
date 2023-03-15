<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ItemProvider;

use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

class Category implements ItemProviderInterface
{
    /**
     * CategorySitemapItemResolver constructor.
     *
     * @param ConfigReaderInterface $configReader Config reader
     * @param CategoryFactory $categoryFactory Category factory
     * @param SitemapItemInterfaceFactory $itemFactory Sitemap item factory
     */
    public function __construct(
        private readonly ConfigReaderInterface $configReader,
        private readonly CategoryFactory $categoryFactory,
        private readonly SitemapItemInterfaceFactory $itemFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->categoryFactory->create()
            ->getCollection($storeId);

        $items = array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create([
                'url' => $item->getUrl(),
                'updatedAt' => $item->getUpdatedAt(),
                'images' => $item->getImages(),
                'priority' => $this->configReader->getPriority($storeId),
                'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
            ]);
        }, $collection);

        return $items;
    }
}
