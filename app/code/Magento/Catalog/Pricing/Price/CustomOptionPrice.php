<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Pricing\Price;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * Class OptionPrice
 *
 */
class CustomOptionPrice extends AbstractPrice implements CustomOptionPriceInterface
{
    /**
     * Price model code
     */
    const PRICE_CODE = 'custom_option_price';

    /**
     * @var array
     */
    protected $priceOptions;

    /**
     * Code of parent adjustment to be skipped from calculation
     *
     * @var string
     */
    protected $excludeAdjustment = null;

    /**
     * @param SaleableInterface $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $excludeAdjustment
     */
    public function __construct(
        SaleableInterface $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        $excludeAdjustment = null
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->excludeAdjustment = $excludeAdjustment;
    }

    /**
     * Get minimal and maximal option values
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getValue()
    {
        $optionValues = [];
        $options = $this->product->getOptions();
        if ($options) {
            /** @var $optionItem \Magento\Catalog\Model\Product\Option */
            foreach ($options as $optionItem) {
                $min = null;
                if (!$optionItem->getIsRequire()) {
                    $min = 0.;
                }
                $max = 0.;
                if ($optionItem->getValues() === null && $optionItem->getPrice() !== null) {
                    $price = $optionItem->getPrice($optionItem->getPriceType() == Value::TYPE_PERCENT);
                    if ($min === null) {
                        $min = $price;
                    } elseif ($price < $min) {
                        $min = $price;
                    }
                    if ($price > $max) {
                        $max = $price;
                    }
                } else {
                    /** @var $optionValue \Magento\Catalog\Model\Product\Option\Value */
                    foreach ($optionItem->getValues() as $optionValue) {
                        $price = $optionValue->getPrice($optionValue->getPriceType() == Value::TYPE_PERCENT);
                        if ($min === null) {
                            $min = $price;
                        } elseif ($price < $min) {
                            $min = $price;
                        }
                        $type = $optionItem->getType();
                        if ($type == Option::OPTION_TYPE_CHECKBOX || $type == Option::OPTION_TYPE_MULTIPLE) {
                            $max += $price;
                        } elseif ($price > $max) {
                            $max = $price;
                        }
                    }
                }
                $optionValues[] = [
                    'option_id' => $optionItem->getId(),
                    'type' => $optionItem->getType(),
                    'min' => ($min === null) ? 0. : $min,
                    'max' => $max,
                ];
            }
        }
        return $optionValues;
    }

    /**
     * @param float $amount
     * @param null|bool|string|array $exclude
     * @param null|array $context
     * @return AmountInterface|bool|float
     */
    public function getCustomAmount($amount = null, $exclude = null, $context = [])
    {
        if (null !== $amount) {
            $amount = $this->priceCurrency->convertAndRound($amount);
        } else {
            $amount = $this->getValue();
        }
        $exclude = $this->excludeAdjustment;
        return $this->calculator->getAmount($amount, $this->getProduct(), $exclude, $context);
    }

    /**
     * Return the minimal or maximal price for custom options
     *
     * @param bool $getMin
     * @return float
     */
    public function getCustomOptionRange($getMin)
    {
        $optionValue = 0.;
        $options = $this->getValue();
        foreach ($options as $option) {
            if ($getMin) {
                $optionValue += $option['min'];
            } else {
                $optionValue += $option['max'];
            }
        }
        return $this->priceCurrency->convertAndRound($optionValue);
    }

    /**
     * Return price for select custom options
     *
     * @return float
     */
    public function getSelectedOptions()
    {
        if (null !== $this->value) {
            return $this->value;
        }
        $this->value = false;
        $optionIds = $this->product->getCustomOption('option_ids');
        if (!$optionIds) {
            return $this->value;
        }
        $this->value = 0.;

        if ($optionIds) {
            $values = explode(',', $optionIds->getValue());
            $values = array_filter($values);
            if (!empty($values)) {
                $this->value = $this->processOptions($values);
            }
        }
        return $this->value;
    }

    /**
     * Process Product Options
     *
     * @param array $values
     * @return float
     */
    protected function processOptions(array $values)
    {
        $value = 0.;
        foreach ($values as $optionId) {
            $option = $this->product->getOptionById($optionId);
            if (!$option) {
                continue;
            }
            $confItemOption = $this->product->getCustomOption('option_' . $option->getId());

            $group = $option->groupFactory($option->getType())
                ->setOption($option)
                ->setConfigurationItemOption($confItemOption);
            $value += $group->getOptionPrice($confItemOption->getValue(), $this->value);
        }
        return $value;
    }

    /**
     * Get Product Options
     *
     * @return array
     */
    public function getOptions()
    {
        if (null !== $this->priceOptions) {
            return $this->priceOptions;
        }
        $this->priceOptions = [];
        $options = $this->product->getOptions();
        if ($options) {
            /** @var $optionItem \Magento\Catalog\Model\Product\Option */
            foreach ($options as $optionItem) {
                /** @var $optionValue \Magento\Catalog\Model\Product\Option\Value */
                foreach ($optionItem->getValues() as $optionValue) {
                    $price = $optionValue->getPrice($optionValue->getPriceType() == Value::TYPE_PERCENT);
                    $this->priceOptions[$optionValue->getId()][$price] = [
                        'base_amount' => $price,
                        'adjustment' => $this->getCustomAmount($price)->getValue(),
                    ];
                }
            }
        }
        return $this->priceOptions;
    }
}
