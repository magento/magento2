<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Swatches\Model\Swatch;

/**
 * Class Product for changing image roles list
 */
class Product
{
    /**
     * Unset swatch image role if product is not simple
     *
     * @param ModelProduct $product
     * @param array|string $imageRoles
     * @return array
     */
    public function afterGetMediaAttributes(ModelProduct $product, $imageRoles)
    {
        if ($product->getTypeId() !== ProductType::TYPE_SIMPLE
            && $product->getTypeId() !== ProductType::TYPE_VIRTUAL
        ) {
            if (is_array($imageRoles)) {
                unset($imageRoles[Swatch::SWATCH_IMAGE_NAME]);
            }
        }

        return $imageRoles;
    }
}
