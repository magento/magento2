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

namespace Magento\Framework\Pricing\Amount;

/**
 * Amount interface
 */
interface AmountInterface
{
    /**
     * Return full amount value
     *
     * @param null|string|array $exclude
     * @return float
     */
    public function getValue($exclude = null);

    /**
     * Return full amount value in string format
     *
     * @return string
     */
    public function __toString();

    /**
     * Return base amount part value
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Return adjustment amount part value by adjustment code
     *
     * @param string $adjustmentCode
     * @return float
     */
    public function getAdjustmentAmount($adjustmentCode);

    /**
     * Return sum amount of all applied adjustments
     *
     * @return float
     */
    public function getTotalAdjustmentAmount();

    /**
     * Return all applied adjustments as array
     *
     * @return float[]
     */
    public function getAdjustmentAmounts();

    /**
     * Check if adjustment is contained in amount object
     *
     * @param string $adjustmentCode
     * @return boolean
     */
    public function hasAdjustment($adjustmentCode);
}
