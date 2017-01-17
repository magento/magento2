<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Adjustment;

use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\SaleableInterface;

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
     * @param null|bool|string|array $exclude
     * @param null|array $context
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAmount($amount, SaleableInterface $saleableItem, $exclude = null, $context = [])
    {
        $baseAmount = $fullAmount = $amount;
        $previousAdjustments = 0;
        $adjustments = [];
        foreach ($saleableItem->getPriceInfo()->getAdjustments() as $adjustment) {
            $code = $adjustment->getAdjustmentCode();
            $toExclude = false;
            if (!is_array($exclude)) {
                if ($exclude === true || ($exclude !== null && $code === $exclude)) {
                    $toExclude = true;
                }
            } else {
                if (in_array($code, $exclude)) {
                    $toExclude = true;
                }
            }
            if ($adjustment->isIncludedInBasePrice()) {
                $adjust = $adjustment->extractAdjustment($baseAmount, $saleableItem, $context);
                $baseAmount -= $adjust;
                $fullAmount = $adjustment->applyAdjustment($fullAmount, $saleableItem, $context);
                $adjust = $fullAmount - $baseAmount - $previousAdjustments;
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
                $previousAdjustments += $adjust;
            }
        }

        return $this->amountFactory->create($fullAmount, $adjustments);
    }
}
