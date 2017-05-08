<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Product\Variations;

use Magento\Catalog\Model\Product;

class Media {

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    private $swatchHelper;

    /**
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     */
    public function __construct(
        \Magento\Swatches\Helper\Data $swatchHelper
    ) {
        $this->swatchHelper = $swatchHelper;
    }

    public function getProductVariationWithMedia(
        Product $configurable,
        array $attributes = [],
        array $additionalAttributes = []
    ) {
        if (!empty($attributes) || !empty($additionalAttributes)) {
            $product = $this->getProduct($configurable, $attributes, $additionalAttributes);
        }

        if (empty($product) || (!$product->getImage() || $product->getImage() === 'no_selection')) {
            $product = $configurable;
        }

        return $this->swatchHelper->getProductMediaGallery($product);
    }

    private function getProduct(Product $configurable, array $attributes = [], array $additionalAttributes = [])
    {
        $product = null;
        $layeredAttributes = [];
        $configurableAttributes = $this->swatchHelper->getAttributesFromConfigurable($configurable);
        if ($configurableAttributes) {
            $layeredAttributes = $this->getLayeredAttributesIfExists($configurableAttributes, $additionalAttributes);
        }
        $resultAttributes = array_merge($layeredAttributes, $attributes);

        $product = $this->swatchHelper->loadVariationByFallback($configurable, $resultAttributes);
        if (!$product || (!$product->getImage() || $product->getImage() == 'no_selection')) {
            $product = $this->swatchHelper->loadFirstVariationWithSwatchImage($configurable, $resultAttributes);
        }
        if (!$product) {
            $product = $this->swatchHelper->loadFirstVariationWithImage($configurable, $resultAttributes);
        }
        return $product;
    }

    private function getLayeredAttributesIfExists(array $configurableAttributes, array $additionalAttributes)
    {
        $layeredAttributes = [];

        $commonAttributeCodes = array_intersect(
            array_column($configurableAttributes, 'attribute_code'),
            array_keys($additionalAttributes)
        );

        foreach ($commonAttributeCodes as $attributeCode) {
            $layeredAttributes[$attributeCode] = $additionalAttributes[$attributeCode];
        }

        return $layeredAttributes;
    }
}
