<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;

/**
 * Bundle calculator interface
 */
interface BundleCalculatorInterface extends CalculatorInterface
{
    /**
     * @param float|string $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaxAmount($amount, Product $saleableItem, $exclude = null);

    /**
     * @param float|string $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaxRegularAmount($amount, Product $saleableItem, $exclude = null);

    /**
     * @param float|string $amount
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinRegularAmount($amount, Product $saleableItem, $exclude = null);

    /**
     * Option amount calculation for saleable item
     *
     * @param Product $saleableItem
     * @param null|bool|string|array $exclude
     * @param bool $searchMin
     * @param \Magento\Framework\Pricing\Amount\AmountInterface|null $bundleProductAmount
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getOptionsAmount(
        Product $saleableItem,
        $exclude = null,
        $searchMin = true,
        $bundleProductAmount = null
    );

    /**
     * Calculate amount for bundle product with all selection prices
     *
     * @param float $basePriceValue
     * @param Product $bundleProduct
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $selectionPriceList
     * @param null|bool|string|array $exclude code of adjustment that has to be excluded
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function calculateBundleAmount($basePriceValue, $bundleProduct, $selectionPriceList, $exclude = null);

    /**
     * Create selection price list for the retrieved options
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param Product $bundleProduct
     * @param bool $useRegularPrice
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    public function createSelectionPriceList($option, $bundleProduct, $useRegularPrice = false);

    /**
     * Find minimal or maximal price for existing options
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $selectionPriceList
     * @param bool $searchMin
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    public function processOptions($option, $selectionPriceList, $searchMin = true);

    /**
     * @param float $amount
     * @param Product $saleableItem
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmountWithoutOption($amount, Product $saleableItem);
}
