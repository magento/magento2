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

namespace Magento\Framework\Pricing\Adjustment;

use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\Object\SaleableInterface;

/**
 * Class Calculator
 */
class Calculator implements CalculatorInterface
{
    /**
     * @var AmountFactory
     */
    protected $amountFactory;

    /**
     * @param AmountFactory $amountFactory
     */
    public function __construct(AmountFactory $amountFactory)
    {
        $this->amountFactory = $amountFactory;
    }

    /**
     * Retrieve Amount object based on given float amount, product and exclude option.
     * It is possible to pass "true" or adjustment code to exclude all or specific adjustment from an amount.
     *
     * @param float|string $amount
     * @param SaleableInterface $saleableItem
     * @param null|bool|string $exclude
     * @param null|array $context
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount($amount, SaleableInterface $saleableItem, $exclude = null, $context = [])
    {
        $baseAmount = $fullAmount = $amount;
        $adjustments = [];
        foreach ($saleableItem->getPriceInfo()->getAdjustments() as $adjustment) {
            $code = $adjustment->getAdjustmentCode();
            $toExclude = false;
            if ($exclude === true || ($exclude !== null && $code === $exclude)) {
                $toExclude = true;
            }
            if ($adjustment->isIncludedInBasePrice()) {
                $adjust = $adjustment->extractAdjustment($baseAmount, $saleableItem, $context);
                $baseAmount -= $adjust;
                $fullAmount = $adjustment->applyAdjustment($fullAmount, $saleableItem, $context);
                $adjust = $fullAmount - $baseAmount;
                if (!$toExclude) {
                    $adjustments[$code] = $adjust;
                }
            } elseif ($adjustment->isIncludedInDisplayPrice($saleableItem)) {
                if ($toExclude) {
                    continue;
                }
                $newAmount = $adjustment->applyAdjustment($fullAmount, $saleableItem, $context);
                $adjust = $newAmount - $fullAmount;
                $adjustments[$code] = $adjust;
                $fullAmount = $newAmount;
            }
        }

        return $this->amountFactory->create($fullAmount, $adjustments);
    }
}
