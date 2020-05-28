<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\Selection;
use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Bundle option price model with final price.
 */
class BundleOptionPrice extends AbstractPrice implements BundleOptionPriceInterface
{
    /**
     * Price model code
     */
    const PRICE_CODE = 'bundle_option';

    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;

    /**
     * @var float|bool|null
     */
    protected $maximalPrice;

    /**
     * @var BundleOptions
     */
    private $bundleOptions;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param BundleOptions $bundleOptions
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        BundleCalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        BundleOptions $bundleOptions
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->product->setQty($this->quantity);
        $this->bundleOptions = $bundleOptions;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        if (null === $this->value) {
            $this->value = $this->bundleOptions->calculateOptions($this->product);
        }

        return $this->value;
    }

    /**
     * Get Options with attached Selections collection.
     *
     * @return Collection
     */
    public function getOptions()
    {
        return $this->bundleOptions->getOptions($this->product);
    }

    /**
     * Get selection amount.
     *
     * @param Selection $selection
     *
     * @return AmountInterface
     */
    public function getOptionSelectionAmount($selection)
    {
        return $this->bundleOptions->getOptionSelectionAmount(
            $this->product,
            $selection,
            false
        );
    }

    /**
     * Calculate maximal or minimal options value.
     *
     * @param bool $searchMin
     *
     * @return bool|float
     */
    protected function calculateOptions($searchMin = true)
    {
        return $this->bundleOptions->calculateOptions($this->product, $searchMin);
    }

    /**
     * Get minimal amount of bundle price with options
     *
     * @return AmountInterface
     */
    public function getAmount()
    {
        return $this->calculator->getOptionsAmount($this->product);
    }
}
