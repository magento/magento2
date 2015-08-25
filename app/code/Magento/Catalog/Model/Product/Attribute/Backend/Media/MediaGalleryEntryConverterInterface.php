<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface MediaGalleryEntryConverterInterface. Create Media Gallery Entry and extract Entry data
 */
interface MediaGalleryEntryConverterInterface
{
    /**
     * Return Media Gallery Entry type
     *
     * @return string
     */
    public function getMediaEntryType();

    /**
     * Create Media Gallery Entry entity from a row input data
     *
     * @param ProductInterface $product
     * @return ProductAttributeMediaGalleryEntryInterface[]
     */
    public function convertTo(ProductInterface $product);

    /**
     * Convert given Media Gallery Entry to raw data collection
     *
     * @param ProductAttributeMediaGalleryEntryInterface $entry
     * @return array
     */
    public function convertFrom(ProductAttributeMediaGalleryEntryInterface $entry);
}
