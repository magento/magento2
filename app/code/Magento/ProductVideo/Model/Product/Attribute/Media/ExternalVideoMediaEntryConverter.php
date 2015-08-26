<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Product\Attribute\Media;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageMediaEntryConverter;
use Magento\Catalog\Model\Product;

/**
 * Converter for External Video media gallery type
 */
class ExternalVideoMediaEntryConverter extends ImageMediaEntryConverter
{
    /**
     * Media Entry type code
     */
    const MEDIA_TYPE_CODE = 'external-video';

    /**
     * {@inheritdoc}
     */
    public function getMediaEntryType()
    {
        return self::MEDIA_TYPE_CODE;
    }

    /**
     * {@inheritdoc}
     */
    public function convertTo(Product $product, array $rowData)
    {
        $previewImageEntry = parent::convertTo($product, $rowData);
        // TODO: Implement convertTo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function convertFrom(ProductAttributeMediaGalleryEntryInterface $entry)
    {
        // TODO: Implement convertFrom() method.
        $dataFromPreviewImageEntry = parent::convertFrom($entry);
        // TODO: Merge data from image and video
    }

}
