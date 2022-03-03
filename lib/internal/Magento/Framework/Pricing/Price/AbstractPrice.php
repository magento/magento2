<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\PriceInfoInterface;

/**
 * Class AbstractPrice
 * Should be the base for creating any Price type class
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractPrice implements PriceInterface
{
    /**
     * Default price type
     */
    public const PRICE_CODE = 'abstract_price';

    /**
     * @var AmountInterface[]
     */
    protected $amount;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\CalculatorInterface
     */
    protected $calculator;

    /**
     * @var SaleableInterface
     */
    protected $product;

    /**
     * @var string
     */
    protected $priceType;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var PriceInfoInterface
     */
    protected $priceInfo;

    /**
     * @var bool|float
     */
    protected $value;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param SaleableInterface $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        SaleableInterface $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->product = $saleableItem;
        $this->quantity = $quantity;
        $this->calculator = $calculator;
        $this->priceCurrency = $priceCurrency;
        $this->priceInfo = $saleableItem->getPriceInfo();
    }

    /**
     * Get price value in display currency
     *
     * @return float|bool
     */
    abstract public function getValue();

    /**
     * Get Price Amount object
     *
     * @return AmountInterface
     */
    public function getAmount()
    {
        $valueKey = (string) $this->getValue();
        if (!isset($this->amount[$valueKey])) {
            $this->amount[$valueKey] = $this->calculator->getAmount($this->getValue(), $this->getProduct());
        }
        return $this->amount[$valueKey];
    }

    /**
     * Calculates custom amount
     *
     * @param float $amount
     * @param null|bool|string|array $exclude
     * @param null|array $context
     *
     * @return AmountInterface|bool|float
     */
    public function getCustomAmount($amount = null, $exclude = null, $context = [])
    {
        if (null !== $amount) {
            $amount = $this->priceCurrency->convertAndRound($amount);
        } else {
            $amount = $this->getValue();
        }
        return $this->calculator->getAmount($amount, $this->getProduct(), $exclude, $context);
    }

    /**
     * Get price type code
     *
     * @return string
     */
    public function getPriceCode()
    {
        return static::PRICE_CODE;
    }

    /**
     * Returns product
     *
     * @return SaleableInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
