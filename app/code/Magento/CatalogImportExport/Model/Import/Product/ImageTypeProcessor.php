<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

class ImageTypeProcessor
{
    /**
     * @return array
     */
    public function getImageTypes()
    {
        return ['image', 'small_image', 'thumbnail', 'swatch_image', '_media_image'];
    }
}
