<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice
{
    /** @var PriceResolverInterface */
    protected $priceResolver;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $saleableItem
     * @param float $quantity
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param PriceResolverInterface $priceResolver
     */
    public function __construct(
        \Magento\Framework\Pricing\SaleableInterface $saleableItem,
        $quantity,
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        PriceResolverInterface $priceResolver
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->priceResolver = $priceResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        if ($this->product->getSelectedConfigurableOption()) {
            $this->amount = null;
        }
        return parent::getAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $selectedConfigurableOption = $this->product->getSelectedConfigurableOption();
        $productId = $selectedConfigurableOption ? $selectedConfigurableOption->getId() : $this->product->getId();
        if (!isset($this->values[$productId])) {
            $this->values[$productId] = $this->priceResolver->resolvePrice($this->product);
        }

        return $this->values[$productId];
    }
}
