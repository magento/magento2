<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

namespace Magento\Sitemap\Model\ItemProvider;

use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

/**
 * Class for adding Store Url in sitemap
 */
class StoreUrl implements ItemProviderInterface
{
    /**
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * StoreUrlSitemapItemResolver constructor.
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
     * @inheritdoc
     */
    public function getItems($storeId)
    {
        $items[] = $this->itemFactory->create($this->getItemData($storeId));

        return $items;
    }

    /**
     * Get constructor arguments for the sitemap item factory.
     *
     * @param int $storeId
     * @return array
     */
    public function getItemData($storeId)
    {
        return [
            'url' => '',
            'priority' => $this->configReader->getPriority($storeId),
            'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
        ];
    }
}
