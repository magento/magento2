<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\AbstractPrice;
<<<<<<< HEAD

/**
 * Bundle option price model with final price
=======
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Bundle option price model with final price.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class BundleOptionRegularPrice extends AbstractPrice implements BundleOptionPriceInterface
{
    /**
<<<<<<< HEAD
     * Price model code
=======
     * Price model code.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    const PRICE_CODE = 'bundle_option_regular_price';

    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;

    /**
<<<<<<< HEAD
     * @var \Magento\Bundle\Pricing\Price\BundleOptions
=======
     * @var BundleOptions
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $bundleOptions;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
<<<<<<< HEAD
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
=======
     * @param PriceCurrencyInterface $priceCurrency
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param BundleOptions $bundleOptions
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        BundleCalculatorInterface $calculator,
<<<<<<< HEAD
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
=======
        PriceCurrencyInterface $priceCurrency,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        BundleOptions $bundleOptions
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->product->setQty($this->quantity);
        $this->bundleOptions = $bundleOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (null === $this->value) {
            $this->value = $this->bundleOptions->calculateOptions($this->product);
        }
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $this->value;
    }

    /**
<<<<<<< HEAD
     * Get Options with attached Selections collection
     *
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptions()
=======
     * Get Options with attached Selections collection.
     *
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptions() : \Magento\Bundle\Model\ResourceModel\Option\Collection
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return $this->bundleOptions->getOptions($this->product);
    }

    /**
<<<<<<< HEAD
     * Get selection amount
=======
     * Get selection amount.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
<<<<<<< HEAD
    public function getOptionSelectionAmount($selection)
=======
    public function getOptionSelectionAmount($selection) : \Magento\Framework\Pricing\Amount\AmountInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return $this->bundleOptions->getOptionSelectionAmount(
            $this->product,
            $selection,
            true
        );
    }

    /**
<<<<<<< HEAD
     * Get minimal amount of bundle price with options
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount()
=======
     * Get minimal amount of bundle price with options.
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount() : \Magento\Framework\Pricing\Amount\AmountInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return $this->calculator->getOptionsAmount($this->product);
    }
}
