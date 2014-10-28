<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @param null|bool $exclude
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaxAmount($amount, Product $saleableItem, $exclude = null);

    /**
     * Option amount calculation for saleable item
     *
     * @param Product $saleableItem
     * @param null|string $exclude
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
     * @param null|string $exclude code of adjustment that has to be excluded
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function calculateBundleAmount($basePriceValue, $bundleProduct, $selectionPriceList, $exclude = null);

    /**
     * Create selection price list for the retrieved options
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param Product $bundleProduct
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    public function createSelectionPriceList($option, $bundleProduct);

    /**
     * Find minimal or maximal price for existing options
     *
     * @param \Magento\Bundle\Model\Option $option
     * @param \Magento\Bundle\Pricing\Price\BundleSelectionPrice[] $selectionPriceList
     * @param bool $searchMin
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPrice[]
     */
    public function processOptions($option, $selectionPriceList, $searchMin = true);
}
