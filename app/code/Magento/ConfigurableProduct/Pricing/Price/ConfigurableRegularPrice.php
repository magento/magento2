<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class RegularPrice
 */
class ConfigurableRegularPrice extends AbstractPrice implements ConfigurableRegularPriceInterface
{
    /**
     * Price type
     */
    const PRICE_CODE = 'regular_price';

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $maxRegularAmount;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $minRegularAmount;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $selectedConfigurableOption = $this->product->getSelectedConfigurableOption();
        $productId = $selectedConfigurableOption ? $selectedConfigurableOption->getId() : $this->product->getId();
        if (!isset($this->values[$productId])) {
            $price = null;
            if (!$selectedConfigurableOption) {
                foreach ($this->getUsedProducts() as $product) {
                    if ($price === null || $price > $product->getPrice()) {
                        $price = $product->getPrice();
                    }
                }
            } else {
                $price = $selectedConfigurableOption->getPrice();
            }

            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
            $this->values[$productId] = $priceInCurrentCurrency ? floatval($priceInCurrentCurrency) : false;
        }

        return $this->values[$productId];
    }
    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        if (false) {
            // TODO: need to check simple product assignment
        } else {
            $amount = $this->getMinRegularAmount($this->product);
        }
        return $amount;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxRegularAmount()
    {
        if (null === $this->maxRegularAmount) {
            $this->maxRegularAmount = $this->doGetMaxRegularAmount();
            $this->maxRegularAmount = $this->doGetMaxRegularAmount() ?: false;
        }
        return $this->maxRegularAmount;

    }

    /**
     * Get max regular amount. Template method
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function doGetMaxRegularAmount()
    {
        // TODO: think about quantity
        $maxAmount = null;
        foreach ($this->getUsedProducts() as $product) {
            $childPriceAmount = $product->getPriceInfo()->getPrice(self::PRICE_CODE)->getAmount();
            if (!$maxAmount || ($childPriceAmount->getValue() > $maxAmount->getValue())) {
                $maxAmount = $childPriceAmount;
            }
        }
        return $maxAmount;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinRegularAmount()
    {
        if (null === $this->minRegularAmount) {
            $this->minRegularAmount = $this->doGetMinRegularAmount() ?: false;
        }
        return $this->minRegularAmount;

    }

    /**
     * Get min regular amount. Template method
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function doGetMinRegularAmount()
    {
        // TODO: think about quantity
        $minAmount = null;
        foreach ($this->getUsedProducts() as $product) {
            $childPriceAmount = $product->getPriceInfo()->getPrice(self::PRICE_CODE)->getAmount();
            if (!$minAmount || ($childPriceAmount->getValue() < $minAmount->getValue())) {
                $minAmount = $childPriceAmount;
            }
        }
        return $minAmount;
    }

    /**
     * Get children simple products
     *
     * @return Product[]
     */
    protected function getUsedProducts()
    {
        return $this->product->getTypeInstance()->getUsedProducts($this->product);
    }
}
