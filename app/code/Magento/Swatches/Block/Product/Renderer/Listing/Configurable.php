<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\Product\Renderer\Listing;

use Magento\Catalog\Model\Product;

/**
 * Swatch renderer block in Category page
 *
 * @api
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    /**
     * @return string
     */
    protected function getRendererTemplate()
    {
        return $this->_template;
    }

    /**
     * Render block hook
     *
     * Produce and return block's html output
     *
     * @return string
     * @since 2.1.6
     */
    protected function _toHtml()
    {
        $output = '';
        if ($this->isProductHasSwatchAttribute()) {
            $output = parent::_toHtml();
        }

        return $output;
    }

    /**
     * @return array
     */
    protected function getSwatchAttributesData()
    {
        $result = [];
        $swatchAttributeData = parent::getSwatchAttributesData();
        foreach ($swatchAttributeData as $attributeId => $item) {
            if (!empty($item['used_in_product_listing'])) {
                $result[$attributeId] = $item;
            }
        }
        return $result;
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $this->unsetData('allow_products');
        return parent::getJsonConfig();
    }

    /**
     * Do not load images for Configurable product with swatches due to its loaded by request
     *
     * @return array
     * @since 2.2.0
     */
    protected function getOptionImages()
    {
        return [];
    }

    /**
     * Add images to result json config in case of Layered Navigation is used
     *
     * @return array
     * @since 2.2.0
     */
    protected function _getAdditionalConfig()
    {
        $config = parent::_getAdditionalConfig();
        if (!empty($this->getRequest()->getQuery()->toArray())) {
            $config['preSelectedGallery'] = $this->getProductVariationWithMedia(
                $this->getProduct(),
                $this->getRequest()->getQuery()->toArray()
            );
        }

        return $config;
    }

    /**
     * Get product images for chosen variation based on selected product attributes
     *
     * @param Product $configurableProduct
     * @param array $additionalAttributes
     * @return array
     * @since 2.2.0
     */
    private function getProductVariationWithMedia(
        Product $configurableProduct,
        array $additionalAttributes = []
    ) {
        $configurableAttributes = $this->getLayeredAttributesIfExists($configurableProduct, $additionalAttributes);
        if (!$configurableAttributes) {
            return [];
        }

        $product = $this->swatchHelper->loadVariationByFallback($configurableProduct, $configurableAttributes);

        return $product ? $this->swatchHelper->getProductMediaGallery($product) : [];
    }

    /**
     * Get product attributes which uses in layered navigation and present for given configurable product
     *
     * @param Product $configurableProduct
     * @param array $additionalAttributes
     * @return array
     * @since 2.2.0
     */
    private function getLayeredAttributesIfExists(Product $configurableProduct, array $additionalAttributes)
    {
        $configurableAttributes = $this->swatchHelper->getAttributesFromConfigurable($configurableProduct);

        $layeredAttributes = [];

        $configurableAttributes = array_map(function ($attribute) {
            return $attribute->getAttributeCode();
        }, $configurableAttributes);

        $commonAttributeCodes = array_intersect(
            $configurableAttributes,
            array_keys($additionalAttributes)
        );

        foreach ($commonAttributeCodes as $attributeCode) {
            $layeredAttributes[$attributeCode] = $additionalAttributes[$attributeCode];
        }

        return $layeredAttributes;
    }
}
