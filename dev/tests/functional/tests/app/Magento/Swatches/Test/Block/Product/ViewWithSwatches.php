<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Block\Product;

use Magento\Catalog\Test\Block\Product\View;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Configurable product view block with swatch attributes on frontend product page
 */
class ViewWithSwatches extends View
{
    /**
     * Selector for swatch attribute value
     *
     * @var string
     */
    private $swatchAttributeSelector = '.swatch-attribute.%s .swatch-attribute-selected-option';

    /**
     * Get chosen options from the product view page.
     *
     * @param InjectableFixture $product
     * @return array
     */
    public function getSelectedSwatchOptions(InjectableFixture $product)
    {
        $checkoutData = $product->getCheckoutData();
        $availableAttributes = $product->getConfigurableAttributesData();
        $attributesData = $availableAttributes['attributes_data'];
        $formData = [];
        foreach ($checkoutData['options']['configurable_options'] as $item) {
            $selector = sprintf($this->swatchAttributeSelector, $attributesData[$item['title']]['attribute_code']);
            $this->waitForElementVisible($selector);
            $selected = $this->_rootElement->find($selector)->getText();
            $formData[$item['title']] = $selected;
        }

        return $formData;
    }
}
