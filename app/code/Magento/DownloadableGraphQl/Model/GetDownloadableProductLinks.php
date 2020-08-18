<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\ResourceModel\Link\Collection;
use Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory;

/**
 * Returns links of a particular downloadable product
 */
class GetDownloadableProductLinks
{
    /**
     * @var CollectionFactory
     */
    private $linkCollectionFactory;

    /**
     * @param CollectionFactory $linkCollectionFactory
     */
    public function __construct(
        CollectionFactory $linkCollectionFactory
    ) {
        $this->linkCollectionFactory = $linkCollectionFactory;
    }

    /**
     * Returns downloadable product links
     *
     * @param Product $product
     * @param array $selectedLinksIds
     * @return LinkInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Product $product, array $selectedLinksIds = []): array
    {
        /** @var Collection */
        $links = $this->linkCollectionFactory->create();
        $links->addTitleToResult($product->getStoreId())
            ->addPriceToResult($product->getStore()->getWebsiteId())
            ->addProductToFilter($product->getId());

        if (count($selectedLinksIds) > 0) {
            $links->addFieldToFilter('main_table.link_id', ['in' => $selectedLinksIds]);
        }
        return $links->getItems();
    }
}
