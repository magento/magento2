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

namespace Magento\Weee\Pricing;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Object\SaleableInterface;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Tax\Pricing\Adjustment as TaxAdjustment;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;

/**
 * Weee pricing adjustment
 */
class Adjustment implements AdjustmentInterface
{
    /**
     * Adjustment code weee
     */
    const ADJUSTMENT_CODE = 'weee';

    /**
     * Weee helper
     *
     * @var WeeeHelper
     */
    protected $weeeHelper;

    /**
     * Sort order
     *
     * @var int|null
     */
    protected $sortOrder;

    /**
     * Constructor
     *
     * @param WeeeHelper $weeeHelper
     * @param int $sortOrder
     */
    public function __construct(WeeeHelper $weeeHelper, $sortOrder = null)
    {
        $this->weeeHelper = $weeeHelper;
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get adjustment code
     *
     * @return string
     */
    public function getAdjustmentCode()
    {
        return self::ADJUSTMENT_CODE;
    }

    /**
     * Define if adjustment is included in base price
     * (FPT is excluded from base price)
     *
     * @return bool
     */
    public function isIncludedInBasePrice()
    {
        return false;
    }

    /**
     * Define if adjustment is included in display price
     *
     * @return bool
     */
    public function isIncludedInDisplayPrice()
    {
        return $this->weeeHelper->typeOfDisplay(
            [
                \Magento\Weee\Model\Tax::DISPLAY_INCL,
                \Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR,
                \Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL
            ]
        );
    }

    /**
     * Extract adjustment amount from the given amount value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     */
    public function extractAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        if (isset($context[CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG])) {
            return 0;
        }
        return $this->getAmount($saleableItem);
    }

    /**
     * Apply adjustment amount and return result value
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|array $context
     * @return float
     */
    public function applyAdjustment($amount, SaleableInterface $saleableItem, $context = [])
    {
        if (isset($context[CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG])) {
            return $amount;
        }
        return $amount + $this->getAmount($saleableItem);
    }

    /**
     * Check if adjustment should be excluded from calculations along with the given adjustment
     *
     * @param string $adjustmentCode
     * @return bool
     */
    public function isExcludedWith($adjustmentCode)
    {
        return (($adjustmentCode == self::ADJUSTMENT_CODE) || ($adjustmentCode == TaxAdjustment::ADJUSTMENT_CODE));
    }

    /**
     * Obtain amount
     *
     * @param SaleableInterface $saleableItem
     * @return float
     */
    protected function getAmount(SaleableInterface $saleableItem)
    {
        return $this->weeeHelper->getAmount($saleableItem);
    }

    /**
     * Return sort order position
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->weeeHelper->isTaxable() ? $this->sortOrder : -1;
    }
}
