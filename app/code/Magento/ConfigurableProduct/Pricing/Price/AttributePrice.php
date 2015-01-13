<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
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
     * @var \Magento\Store\Model\StoreManagerInterface
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
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param PriceModifierInterface $modifier
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        PriceModifierInterface $modifier,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->priceModifier = $modifier;
        $this->storeManager = $storeManager;
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
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
                'options' => $this->getPriceOptions($attributeId, $attribute, $options),
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
            $optionValueAmount = $this->getOptionValueAmount($value);
            $oldPrice = $optionValueAmount->getValue();

            $optionValueModified = $this->getOptionValueModified($value);
            $basePrice = $optionValueModified->getBaseAmount();
            $finalPrice = $optionValueModified->getValue();

            $optionPrices[] = [
                'id' => $value['value_index'],
                'label' => $value['label'],
                'prices' => [
                    'oldPrice' => [
                        'amount' => $this->convertDot($oldPrice),
                    ],
                    'basePrice' => [
                        'amount' => $this->convertDot($basePrice),
                    ],
                    'finalPrice' => [
                        'amount' => $this->convertDot($finalPrice),
                    ],
                ],
                'products' => $this->getProductsIndex($attributeId, $options, $value),
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
        $pricingValue = $this->getPricingValue($value, \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE);
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
        $amount = $this->getPricingValue($value, \Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE);

        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        return $this->calculator->getAmount(floatval($amount), $this->product, null, $context);
    }

    /**
     * Prepare percent price value
     *
     * @param array $value
     * @param string $priceCode
     * @return float
     */
    protected function preparePrice(array $value, $priceCode)
    {
        return $this->product
            ->getPriceInfo()
            ->getPrice($priceCode)
            ->getValue() * $value['pricing_value'] / 100;
    }

    /**
     * Get value from array
     *
     * @param array $value
     * @param string $priceCode
     * @return float
     */
    protected function getPricingValue(array $value, $priceCode)
    {
        if ($value['is_percent'] && !empty($value['pricing_value'])) {
            return $this->preparePrice($value, $priceCode);
        } else {
            return $this->priceCurrency->convertAndRound($value['pricing_value']);
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
     * Get price value
     *
     * @return float|bool
     */
    public function getValue()
    {
        // TODO: Implement getValue() method.
    }
}
