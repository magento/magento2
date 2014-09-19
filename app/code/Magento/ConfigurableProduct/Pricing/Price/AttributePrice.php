<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\ConfigurableProduct\Block\Product\View;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class PriceOptions
 *
 */
class AttributePrice extends AbstractPrice implements AttributePriceInterface
{
    /**
     * Default price type
     */
    const PRICE_CODE = 'attribute_price';

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param PriceModifierInterface $modifier
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceModifierInterface $modifier,
        \Magento\Framework\StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->priceModifier = $modifier;
        $this->storeManager = $storeManager;
        parent::__construct($saleableItem, $quantity, $calculator);
    }

    /**
     * Prepare JsonAttributes with Options Prices
     *
     * @param array $options
     * @return array
     */
    public function prepareAttributes(array $options = [])
    {
        $defaultValues = [];
        $attributes = [];
        $configurableAttributes = $this->product->getTypeInstance()->getConfigurableAttributes($this->product);
        foreach ($configurableAttributes as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info = [
                'id' => $attributeId,
                'code' => $productAttribute->getAttributeCode(),
                'label' => $attribute->getLabel(),
                'options' => $this->getPriceOptions($attributeId, $attribute, $options)
            ];
            $defaultValues[$attributeId] = $this->getAttributeConfigValue($attributeId);
            if ($this->validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }
        }
        return [
            'priceOptions' => $attributes,
            'defaultValues' => $defaultValues
        ];
    }

    /**
     * Returns prices for configurable product options
     *
     * @param int $attributeId
     * @param Attribute $attribute
     * @param array $options
     * @return array
     */
    public function getPriceOptions($attributeId, $attribute, array $options = [])
    {
        $prices = $attribute->getPrices();
        $optionPrices = [];
        if (!is_array($prices)) {
            return $optionPrices;
        }

        foreach ($prices as $value) {
            $optionValueModified = $this->getOptionValueModified($value);
            $optionValueAmount = $this->getOptionValueAmount($value);

            $price = $this->convertPrice($optionValueAmount->getValue());
            $optionPrices[] = [
                'id' => $value['value_index'],
                'label' => $value['label'],
                'price' => $this->convertDot($optionValueModified->getValue()),
                'oldPrice' => $this->convertDot($price),
                'inclTaxPrice' => $this->convertDot($optionValueModified->getValue()),
                'exclTaxPrice' => $this->convertDot($optionValueModified->getBaseAmount()),
                'products' => $this->getProductsIndex($attributeId, $options, $value)
            ];
        }

        return $optionPrices;
    }

    /**
     * Get Option Value including price rule
     *
     * @param array $value
     * @return AmountInterface
     */
    public function getOptionValueModified(
        array $value = []
    ) {
        $pricingValue = $this->getPricingValue($value);
        $this->product->setParentId(true);
        $amount = $this->priceModifier->modifyPrice($pricingValue, $this->product);

        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        return $this->calculator->getAmount(floatval($amount), $this->product, null, $context);
    }

    /**
     * Get Option Value Amount with no Catalog Rules
     *
     * @param array $value
     * @return AmountInterface
     */
    public function getOptionValueAmount(
        array $value = []
    ) {
        $amount = $this->getPricingValue($value);

        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        return $this->calculator->getAmount(floatval($amount), $this->product, null, $context);
    }

    /**
     * Prepare percent price value
     *
     * @param array $value
     * @return float
     */
    protected function preparePrice(array $value = [])
    {
        return $this->product
            ->getPriceInfo()
            ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
            ->getValue() * $value['pricing_value'] / 100;
    }

    /**
     * Get value from array
     *
     * @param array $value
     * @return float
     */
    protected function getPricingValue(array $value = [])
    {
        if ($value['is_percent'] && !empty($value['pricing_value'])) {
            return $this->preparePrice($value);
        } else {
            return $value['pricing_value'];
        }
    }

    /**
     * Get Products Index
     *
     * @param int $attributeId
     * @param array $options
     * @param array $value
     * @return array
     */
    protected function getProductsIndex($attributeId, array $options = [], array $value = [])
    {
        if (isset($options[$attributeId][$value['value_index']])) {
            return $options[$attributeId][$value['value_index']];
        } else {
            return [];
        }
    }

    /**
     * @param int $attributeId
     * @return mixed|null
     */
    protected function getAttributeConfigValue($attributeId)
    {
        if ($this->product->hasPreconfiguredValues()) {
            return $this->product->getPreconfiguredValues()->getData('super_attribute/' . $attributeId);
        }
    }

    /**
     * Validation of super product option
     *
     * @param array $info
     * @return bool
     */
    protected function validateAttributeInfo($info)
    {
        if (count($info['options']) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Replace ',' on '.' for js
     *
     * @param float $price
     * @return string
     */
    protected function convertDot($price)
    {
        return str_replace(',', '.', $price);
    }


    /**
     * Convert price from default currency to current currency
     *
     * @param float $price
     * @param bool $round
     * @return float
     */
    protected function convertPrice($price, $round = false)
    {
        if (empty($price)) {
            return 0;
        }

        $price = $this->priceCurrency->convert($price);
        if ($round) {
            $price = $this->priceCurrency->round($price);
        }

        return $price;
    }

    /**
     * Returns tax config for Configurable options
     *
     * @param int|null $customerId
     * @return array
     */
    public function getTaxConfig($customerId)
    {
        $config = $this->prepareAdjustmentConfig($customerId);
        unset($config['product']);
        return $config;
    }

    /**
     * Default values for configurable options
     *
     * @param int|null $customerId
     * @return array
     */
    public function prepareAdjustmentConfig($customerId)
    {
        //pass customer
        return [
            'includeTax' => false,
            'showIncludeTax' => false,
            'showBothPrices' => false,
            'defaultTax' => 0,
            'currentTax' => 0,
            'inclTaxTitle' => __('Incl. Tax'),
            'product' => $this->product,
            'customerId' => $customerId
        ];
    }

    /**
     * Get price value
     *
     * @return float|bool
     */
    public function getValue()
    {
        // TODO: Implement getValue() method.
    }
}
