<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View;

use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Class Items
 * Block for items ordered on order page
 */
class Items extends Block
{
    /**
     * Invoice item price xpath selector
     *
     * @var string
     */
    protected $priceSelector = '//td[@class="col-price"]//div[@class="price-excl-tax"]//span[@class="price"]';

    // @codingStandardsIgnoreStart
    /**
     * Product price excluding tax search mask
     *
     * @var string
     */
    protected $itemExclTax = '//tr[contains (.,"%s")]/td[@class="col-price"]/div[@class="price-excl-tax"]/span[@class="price"]';

    /**
     * Product price including tax search mask
     *
     * @var string
     */
    protected $itemInclTax = '//tr[contains (.,"%s")]/td[@class="col-price"]/div[@class="price-incl-tax"]/span[@class="price"]';

    /**
     * Product price subtotal excluding tax search mask
     *
     * @var string
     */
    protected $itemSubExclTax = '//tr[contains (.,"%s")]/td[@class="col-subtotal"]/div[@class="price-excl-tax"]/span[@class="price"]';

    /**
     * Product price subtotal including tax search mask
     *
     * @var string
     */
    protected $itemSubInclTax = '//tr[contains (.,"%s")]/td[@class="col-subtotal"]/div[@class="price-incl-tax"]/span[@class="price"]';
    // @codingStandardsIgnoreEnd

    /**
     * Returns the item price for the specified product.
     *
     * @param InjectableFixture $product
     * @return array|string
     */
    public function getPrice(InjectableFixture $product)
    {
        $productName = $product->getName();

        if ($product instanceof ConfigurableProduct) {
            // Find the price for the specific configurable product that was purchased
            $attributesData = $product->getConfigurableAttributesData()['attributes_data'];
            $matrix = $product->getConfigurableAttributesData()['matrix'];
            $checkoutOptions = $product->getCheckoutData()['options']['configurable_options'];
            $optionsDetails = [];
            $matrixKey = [];
            $optionsPrice = 0;

            foreach ($checkoutOptions as $checkoutOption) {
                $titleKey = $checkoutOption['title'];
                $valueKey = $checkoutOption['value'];
                $attributeName = $attributesData[$titleKey]['frontend_label'];
                $optionLabel = $attributesData[$titleKey]['options'][$valueKey]['label'];
                $optionsDetails[] = "{$attributeName}{$optionLabel}";
                $matrixKey[] = "{$titleKey}:{$valueKey}";

                $optionsPrice += $attributesData[$titleKey]['options'][$valueKey]['pricing_value'];
            }
            $optionsDetails = implode(' ', $optionsDetails);
            $matrixKey = implode(' ', $matrixKey);

            $productDisplay = $productName . 'SKU: ' . $matrix[$matrixKey]['sku'] . $optionsDetails;
        } else {
            $productDisplay = $productName . 'SKU: ' . $product->getSku();
        }

        $selector = '//tr[normalize-space(td)="' . $productDisplay . '"]' . $this->priceSelector;
        return $this->escapeCurrency($this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->getText());
    }

    /**
     * Get Item price excluding tax
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemPriceExclTax($productName)
    {
        $locator = sprintf($this->itemExclTax, $productName);
        $price = $this->_rootElement->find($locator, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item price including tax
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemPriceInclTax($productName)
    {
        $locator = sprintf($this->itemInclTax, $productName);
        $price = $this->_rootElement->find($locator, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item price excluding tax
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemSubExclTax($productName)
    {
        $locator = sprintf($this->itemSubExclTax, $productName);
        $price = $this->_rootElement->find($locator, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item price excluding tax
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemSubInclTax($productName)
    {
        $locator = sprintf($this->itemSubInclTax, $productName);
        $price = $this->_rootElement->find($locator, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Method that escapes currency symbols
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }
}
