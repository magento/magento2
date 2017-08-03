<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\Product;

/**
 * Interface EntryConverterInterface. Create Media Gallery Entry and extract Entry data
 *
 * @api
 */
interface EntryConverterInterface
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
     * @param Product $product
     * @param array $rowData
     * @return ProductAttributeMediaGalleryEntryInterface[]
     */
    public function convertTo(Product $product, array $rowData);

    /**
     * Convert given Media Gallery Entry to raw data collection
     *
     * @param ProductAttributeMediaGalleryEntryInterface $entry
     * @return array
     */
    public function convertFrom(ProductAttributeMediaGalleryEntryInterface $entry);
}
