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

/**
 * Bundle option price model with final price
 */
class BundleOptionRegularPrice extends AbstractPrice implements BundleOptionPriceInterface
{
    /**
     * Price model code
     */
    const PRICE_CODE = 'bundle_option_regular_price';

    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;

    /**
     * @var \Magento\Bundle\Pricing\Price\BundleOptions
     */
    private $bundleOptions;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param BundleOptions $bundleOptions
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        BundleCalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
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
        return $this->value;
    }

    /**
     * Get Options with attached Selections collection
     *
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptions()
    {
        return $this->bundleOptions->getOptions($this->product);
    }

    /**
     * Get selection amount
     *
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getOptionSelectionAmount($selection)
    {
        return $this->bundleOptions->getOptionSelectionAmount(
            $this->product,
            $selection,
            true
        );
    }

    /**
     * Get minimal amount of bundle price with options
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount()
    {
        return $this->calculator->getOptionsAmount($this->product);
    }
}
