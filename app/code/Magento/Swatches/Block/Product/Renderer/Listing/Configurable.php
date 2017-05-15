<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\Product\Renderer\Listing;

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
}
