<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

/**
 * Class Product for changing image roles list
 */
class Product
{
    /**
     * Name of swatch image role
     */
    const ROLE_SWATCH_IMAGE_NAME = 'swatch_image';

    /**
     * Unset swatch image role if product is not simple
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array|string $imageRoles
     * @return array
     */
    public function afterGetMediaAttributes(\Magento\Catalog\Model\Product $product, $imageRoles)
    {
        if ($product->getTypeId() !== \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            && $product->getTypeId() !== \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
        ) {
            if (is_array($imageRoles)) {
                unset($imageRoles[self::ROLE_SWATCH_IMAGE_NAME]);
            }
        }

        return $imageRoles;
    }
}
