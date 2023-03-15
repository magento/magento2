<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ItemProvider;

use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

class Product implements ItemProviderInterface
{
    /**
     * ProductSitemapItemResolver constructor.
     *
     * @param ConfigReaderInterface $configReader Config reader
     * @param ProductFactory $productFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        private readonly ConfigReaderInterface $configReader,
        private readonly ProductFactory $productFactory,
        private readonly SitemapItemInterfaceFactory $itemFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->productFactory->create()
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
