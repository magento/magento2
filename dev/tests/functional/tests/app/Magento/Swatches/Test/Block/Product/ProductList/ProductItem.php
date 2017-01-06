<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Block\Product\ProductList;

use Magento\Mtf\Client\Locator;
use Magento\Catalog\Test\Block\Product\ProductList\ProductItem as CatalogProductItem;

/**
 * Product item block on frontend category view.
 */
class ProductItem extends CatalogProductItem
{
    /**
     * Selector for the swatches of the product.
     *
     * @var string
     */
    protected $swatchSelector = 'div[option-id="%s"]';

    /**
     * Fill product options on category page.
     *
     * @param \Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct $product
     * @return void
     */
    public function fillData(\Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct $product)
    {
        $checkoutData = $product->getCheckoutData();
        $options = $checkoutData['options']['configurable_options'];
        $confAttrData = $product->getDataFieldConfig('configurable_attributes_data');
        $confAttrSource = $confAttrData['source'];
        $attributes = $confAttrSource->getAttributes();

        foreach ($options as $option) {
            if (!isset($attributes[$option['title']])
                || stripos ($attributes[$option['title']]->getFrontendInput(), "swatch") === false
            ) {
                continue;
            }
            $availableOptions = $attributes[$option['title']]->getOptions();
            $optionKey = str_replace('option_key_', '', $option['value']);
            if (!isset($availableOptions[$optionKey])) {
                continue;
            }
            $optionForSelect = $availableOptions[$optionKey];
            $this->clickOnSwatch($optionForSelect['id']);
        }
    }

    /**
     * Click on swatch.
     *
     * @param $optionId
     */
    private function clickOnSwatch($optionId)
    {
        $selector = sprintf($this->swatchSelector, $optionId);
        $this->_rootElement->find($selector, Locator::SELECTOR_CSS)->click();
    }

    /**
     * @inheritdoc
     */
    public function clickAddToCart()
    {
        $this->_rootElement->hover();
        parent::clickAddToCart();
    }
}
