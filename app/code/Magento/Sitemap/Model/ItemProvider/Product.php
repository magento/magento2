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
     * Product factory
     *
     * @var ProductFactory
     */
    private $productFactory;

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
     * ProductSitemapItemResolver constructor.
     *
     * @param ConfigReaderInterface $configReader
     * @param ProductFactory $productFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ConfigReaderInterface $configReader,
        ProductFactory $productFactory,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->productFactory = $productFactory;
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->productFactory->create()
            ->getCollection($storeId);

        return array_map(function ($item) use ($storeId) {
            return $this->itemFactory->create($this->prepareParams($item, $storeId));
        }, $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareParams($item, $storeId)
    {
        return [
            'url' => $item->getUrl(),
            'updatedAt' => $item->getUpdatedAt(),
            'images' => $item->getImages(),
            'priority' => $this->configReader->getPriority($storeId),
            'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
        ];
    }
}
