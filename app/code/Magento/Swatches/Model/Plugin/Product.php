<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

/**
 * Class Product for changing image roles list
 */
class Product
{
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
                unset($imageRoles[\Magento\Swatches\Model\Swatch::SWATCH_IMAGE_NAME]);
            }
        }

        return $imageRoles;
    }
}
