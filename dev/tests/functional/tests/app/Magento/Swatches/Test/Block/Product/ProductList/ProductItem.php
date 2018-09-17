<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Block\Product\ProductList;

use Magento\Catalog\Test\Block\Product\ProductList\ProductItem as CatalogProductItem;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Client\Locator;
use Magento\Swatches\Test\Fixture\SwatchesProductAttribute;

/**
 * Product item block on frontend category view.
 */
class ProductItem extends CatalogProductItem
{
    /**
     * Selector for the swatches blocks of the product.
     *
     * @var string
     */
    protected $swatchBlockSelector = '.swatch-attribute-options';

    /**
     * Selector for the swatches items of the product.
     *
     * @var string
     */
    private $swatchItemSelector = 'div[option-id="%s"]';

    /**
     * Check swatches visibility.
     *
     * @return bool
     */
    public function isSwatchesBlockVisible()
    {
        return $this->_rootElement->find($this->swatchBlockSelector)->isVisible();
    }

    /**
     * Fill product options on category page.
     *
     * @param ConfigurableProduct $product
     * @return void
     */
    public function fillData(ConfigurableProduct $product)
    {
        /** @var array $checkoutData */
        $checkoutData = $product->getCheckoutData();
        /** @var array $options */
        $options = $checkoutData['options']['configurable_options'];
        /** @var array $confAttrData */
        $confAttrData = $product->getDataFieldConfig('configurable_attributes_data');
        /** @var ConfigurableProduct\ConfigurableAttributesData $confAttrSource */
        $confAttrSource = $confAttrData['source'];
        /** @var SwatchesProductAttribute[] $attributes */
        $attributes = $confAttrSource->getAttributes();

        foreach ($options as $option) {
            if (!isset($attributes[$option['title']])) {
                continue;
            }
            /** @var array $availableOptions */
            $availableOptions = $attributes[$option['title']]->getOptions();
            /** @var string $optionKey */
            $optionKey = str_replace('option_key_', '', $option['value']);

            if (!isset($availableOptions[$optionKey])) {
                continue;
            }

            /** @var array $optionForSelect */
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
        /** @var string $selector */
        $selector = sprintf($this->swatchItemSelector, $optionId);
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
