<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Block\Product;

use Magento\Catalog\Test\Block\Product\View;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Configurable product view block with swatch attributes on frontend product page.
 */
class ViewWithSwatches extends View
{
    /**
     * Selector for swatch attribute value.
     *
     * @var string
     */
    private $swatchAttributeSelector = '.swatch-attribute.%s .swatch-attribute-selected-option';

    /**
     * Get chosen options from the product view page.
     *
     * @param FixtureInterface $product
     * @return array
     */
    public function getSelectedSwatchOptions(FixtureInterface $product)
    {
        /** @var array $checkoutData */
        $checkoutData = $product->getCheckoutData();

        /** @var array $availableAttributes */
        $availableAttributes = $product->getConfigurableAttributesData();

        /** @var array $attributesData */
        $attributesData = $availableAttributes['attributes_data'];

        /** @var array $formData */
        $formData = [];

        /** @var array $item */
        foreach ($checkoutData['options']['configurable_options'] as $item) {
            /** @var string $selector */
            $selector = sprintf(
                $this->swatchAttributeSelector,
                $attributesData[$item['title']]['attribute_code']
            );

            $this->waitForElementVisible($selector);

            /** @var string $selected */
            $selected = $this->_rootElement->find($selector)->getText();

            $formData[$item['title']] = $selected;
        }

        return $formData;
    }
}
