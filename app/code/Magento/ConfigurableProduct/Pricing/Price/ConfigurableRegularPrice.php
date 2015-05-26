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
     * @var Configurable
     */
    protected $configurableType;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $maxRegularAmount;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $minRegularAmount;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param Configurable $configurableType
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        Configurable $configurableType
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->configurableType = $configurableType;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (null === $this->value) {
            // TODO:
        }
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        $amount = 0;
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
        $maxAmount = 0;
        foreach ($this->getUsedProducts($this->product) as $product) {
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
            $this->minRegularAmount = $this->doGetMinRegularAmount();
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
        $minAmount = 0;
        foreach ($this->getUsedProducts($this->product) as $product) {
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
        return $this->configurableType->getUsedProducts($this->product);
    }
}
