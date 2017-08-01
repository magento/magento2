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
 * @api
 * @since 2.0.0
 */
abstract class AbstractPrice implements PriceInterface
{
    /**
     * Default price type
     */
    const PRICE_CODE = 'abstract_price';

    /**
     * @var AmountInterface[]
     * @since 2.0.0
     */
    protected $amount;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator
     * @since 2.0.0
     */
    protected $calculator;

    /**
     * @var SaleableInterface
     * @since 2.0.0
     */
    protected $product;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $priceType;

    /**
     * @var float
     * @since 2.0.0
     */
    protected $quantity;

    /**
     * @var PriceInfoInterface
     * @since 2.0.0
     */
    protected $priceInfo;

    /**
     * @var bool|float
     * @since 2.0.0
     */
    protected $value;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @param SaleableInterface $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract public function getValue();

    /**
     * Get Price Amount object
     *
     * @return AmountInterface
     * @since 2.0.0
     */
    public function getAmount()
    {
        if (!isset($this->amount[$this->getValue()])) {
            $this->amount[$this->getValue()] = $this->calculator->getAmount($this->getValue(), $this->getProduct());
        }
        return $this->amount[$this->getValue()];
    }

    /**
     * @param float $amount
     * @param null|bool|string|array $exclude
     * @param null|array $context
     * @return AmountInterface|bool|float
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getPriceCode()
    {
        return static::PRICE_CODE;
    }

    /**
     * @return SaleableInterface
     * @since 2.0.0
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return float
     * @since 2.0.0
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
