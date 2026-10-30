<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Model\ItemProvider\Batch;

use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\ResourceModel\Catalog\Batch\ProductFactory as BatchProductFactory;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

/**
 * Memory-optimized product item provider using batch processing
 */
class Product implements ItemProviderInterface
{
    /**
     * @var BatchProductFactory
     */
    private BatchProductFactory $batchProductFactory;

    /**
     * @var SitemapItemInterfaceFactory
     */
    private SitemapItemInterfaceFactory $itemFactory;

    /**
     * @var ConfigReaderInterface
     */
    private ConfigReaderInterface $configReader;

    /**
     * @param ConfigReaderInterface $configReader
     * @param BatchProductFactory $batchProductFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ConfigReaderInterface $configReader,
        BatchProductFactory $batchProductFactory,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->batchProductFactory = $batchProductFactory;
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
    }

    /**
     * @inheritdoc
     */
    public function getItems($storeId)
    {
        $collection = $this->batchProductFactory->create()
            ->getCollectionArray($storeId);

        if ($collection === false) {
            return [];
        }

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
