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
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Bundle option price model with final price.
=======

/**
 * Bundle option price model with final price
>>>>>>> upstream/2.2-develop
 */
class BundleOptionRegularPrice extends AbstractPrice implements BundleOptionPriceInterface
{
    /**
<<<<<<< HEAD
     * Price model code.
=======
     * Price model code
>>>>>>> upstream/2.2-develop
     */
    const PRICE_CODE = 'bundle_option_regular_price';

    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;

    /**
<<<<<<< HEAD
     * @var BundleOptions
=======
     * @var \Magento\Bundle\Pricing\Price\BundleOptions
>>>>>>> upstream/2.2-develop
     */
    private $bundleOptions;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
<<<<<<< HEAD
     * @param PriceCurrencyInterface $priceCurrency
=======
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
>>>>>>> upstream/2.2-develop
     * @param BundleOptions $bundleOptions
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        BundleCalculatorInterface $calculator,
<<<<<<< HEAD
        PriceCurrencyInterface $priceCurrency,
=======
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
>>>>>>> upstream/2.2-develop
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
>>>>>>> upstream/2.2-develop
        return $this->value;
    }

    /**
<<<<<<< HEAD
     * Get Options with attached Selections collection.
     *
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptions() : \Magento\Bundle\Model\ResourceModel\Option\Collection
=======
     * Get Options with attached Selections collection
     *
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptions()
>>>>>>> upstream/2.2-develop
    {
        return $this->bundleOptions->getOptions($this->product);
    }

    /**
<<<<<<< HEAD
     * Get selection amount.
=======
     * Get selection amount
>>>>>>> upstream/2.2-develop
     *
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
<<<<<<< HEAD
    public function getOptionSelectionAmount($selection) : \Magento\Framework\Pricing\Amount\AmountInterface
=======
    public function getOptionSelectionAmount($selection)
>>>>>>> upstream/2.2-develop
    {
        return $this->bundleOptions->getOptionSelectionAmount(
            $this->product,
            $selection,
            true
        );
    }

    /**
<<<<<<< HEAD
     * Get minimal amount of bundle price with options.
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount() : \Magento\Framework\Pricing\Amount\AmountInterface
=======
     * Get minimal amount of bundle price with options
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount()
>>>>>>> upstream/2.2-develop
    {
        return $this->calculator->getOptionsAmount($this->product);
    }
}
