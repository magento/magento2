<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Block\Product;

use Magento\Catalog\Test\Block\Product\View;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Configurable product view block with swatch attributes on frontend product page
 */
class ViewWithSwatches extends View
{
    /**
     * Selector for all swatch attributes
     *
     * @var string
     */
    private $swatchAttributesSelector = '.swatch-attribute';

    /**
     * Selector for swatch attribute label
     *
     * @var string
     */
    private $swatchAttributesLabelSelector = '.swatch-attribute-label';

    /**
     * Selector for all swatch attribute options
     *
     * @var string
     */
    private $swatchAttributeOptionsSelector = '.swatch-option';

    /**
     * Selector for selected swatch attribute options
     *
     * @var string
     */
    private $selectedSwatchAttributeSelector = '.swatch-attribute.%s .swatch-attribute-selected-option';

    /**
     * Get swatch attributes data from the product page. Key is attribute code
     *
     * @return array
     */
    public function getSwatchAttributesData()
    {
        $this->waitForElementVisible($this->swatchAttributesSelector);

        $swatchAttributesData = [];
        $swatchAttributes = $this->_rootElement->getElements($this->swatchAttributesSelector);
        foreach ($swatchAttributes as $swatchAttribute) {
            $attributeCode = $swatchAttribute->getAttribute('attribute-code');
            $swatchAttributesData[$attributeCode] = [
                'attribute_code' => $attributeCode,
                'attribute_id' => $swatchAttribute->getAttribute('attribute-id'),
                'label' => $swatchAttribute->find($this->swatchAttributesLabelSelector)->getText(),
                'options' => $this->getSwatchAttributeOptionsData($swatchAttribute),
            ];
        }
        return $swatchAttributesData;
    }

    /**
     * Get swatch attribute options data. Key is option id
     *
     * @param ElementInterface $swatchAttribute
     * @return array
     */
    private function getSwatchAttributeOptionsData(ElementInterface $swatchAttribute)
    {
        $optionsData = [];
        $options = $swatchAttribute->getElements($this->swatchAttributeOptionsSelector);
        foreach ($options as $option) {
            $optionId = $option->getAttribute('option-id');
            $optionsData[$optionId] = [
                'option_id' => $optionId,
                'label' => $option->getText(),
            ];
        }
        return $optionsData;
    }

    /**
     * Get chosen options from the product page
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
            $selector = sprintf(
                $this->selectedSwatchAttributeSelector,
                $attributesData[$item['title']]['attribute_code']
            );
            $this->waitForElementVisible($selector);
            $selected = $this->_rootElement->find($selector)->getText();
            $formData[$item['title']] = $selected;
        }

        return $formData;
    }
}
