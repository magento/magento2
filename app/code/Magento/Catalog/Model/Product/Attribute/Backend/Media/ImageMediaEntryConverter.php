<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\Product;

/**
 * Converter for Image media gallery type
 */
class ImageMediaEntryConverter implements MediaGalleryEntryConverterInterface
{
    /**
     * Media Entry type code
     */
    const MEDIA_ENTRY_TYPE_IMAGE = 'image';

    /**
     * {@inheritdoc}
     */
    public function getMediaEntryType()
    {
        return self::MEDIA_ENTRY_TYPE_IMAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function convertTo(Product $product, array $rowData)
    {
        // TODO: Implement convertTo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function convertFrom(ProductAttributeMediaGalleryEntryInterface $entry)
    {
        // TODO: Implement convertFrom() method.
    }

}
