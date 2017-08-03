<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation;

/**
 * Class \Magento\Tax\Model\Calculation\TotalBaseCalculator
 *
 * @since 2.0.0
 */
class TotalBaseCalculator extends AbstractAggregateCalculator
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function roundAmount(
        $amount,
        $rate = null,
        $direction = null,
        $type = self::KEY_REGULAR_DELTA_ROUNDING,
        $round = true,
        $item = null
    ) {
        return $this->deltaRound($amount, $rate, $direction, $type, $round);
    }
}
