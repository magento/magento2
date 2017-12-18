<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\TierPrice;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter\MediaGalleryEntries;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter\Options;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter\TierPrices;

class Formatter
{
    /**
     * @var MediaGalleryEntries
     */
    private $mediaGalleryEntriesFormatter;

    /**
     * @var Options
     */
    private $optionsFormatter;

    /**
     * @var TierPrices
     */
    private $tierPricesFormatter;

    /**
     * @param MediaGalleryEntries $mediaGalleryEntriesFormatter
     * @param Options $optionsFormatter
     * @param TierPrices $tierPricesFormatter
     */
    public function __construct(
        MediaGalleryEntries $mediaGalleryEntriesFormatter,
        Options $optionsFormatter,
        TierPrices $tierPricesFormatter
    ) {
        $this->mediaGalleryEntriesFormatter = $mediaGalleryEntriesFormatter;
        $this->optionsFormatter = $optionsFormatter;
        $this->tierPricesFormatter = $tierPricesFormatter;
    }

    /**
     * Format single product data from object to an array
     *
     * @param Product $product
     * @return array
     */
    public function format(Product $product)
    {
        $productData = $product->getData();
        $productData['id'] = $product->getId();
        unset($productData['entity_id']);

        $productData = $this->mediaGalleryEntriesFormatter->format($product, $productData);
        $productData = $this->optionsFormatter->format($productData);
        $productData = $this->tierPricesFormatter->format($product, $productData);

        return $productData;
    }
}
