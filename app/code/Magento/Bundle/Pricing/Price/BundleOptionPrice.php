<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\App\ObjectManager;

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
     * @var BundleSelectionFactory
     * @deprecated 100.2.3
     */
    protected $selectionFactory;

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
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param BundleSelectionFactory $bundleSelectionFactory
     * @param BundleOptions|null $bundleOptions
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        BundleCalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        BundleSelectionFactory $bundleSelectionFactory,
        BundleOptions $bundleOptions = null
    ) {
        $this->selectionFactory = $bundleSelectionFactory;
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->product->setQty($this->quantity);
        $this->bundleOptions = $bundleOptions ?: ObjectManager::getInstance()->get(BundleOptions::class);
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
     * Getter for maximal price of options.
     *
     * @return bool|float
     * @deprecated 100.2.3
     */
    public function getMaxValue()
    {
        if (null === $this->maximalPrice) {
            $this->maximalPrice = $this->bundleOptions->calculateOptions($this->product, false);
        }

        return $this->maximalPrice;
    }

    /**
     * Get Options with attached Selections collection.
     *
     * @return \Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptions()
    {
        return $this->bundleOptions->getOptions($this->product);
    }

    /**
     * Get selection amount.
     *
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
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
     * @return bool|float
     */
    protected function calculateOptions($searchMin = true)
    {
        return $this->bundleOptions->calculateOptions($this->product, $searchMin);
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
