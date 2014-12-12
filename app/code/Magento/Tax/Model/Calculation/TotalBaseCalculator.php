<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tax\Model\Calculation;


class TotalBaseCalculator extends AbstractAggregateCalculator
{
    /**
     * {@inheritdoc}
     */
    protected function roundAmount(
        $amount,
        $rate = null,
        $direction = null,
        $type = self::KEY_REGULAR_DELTA_ROUNDING,
        $round = true
    ) {
        return $this->deltaRound($amount, $rate, $direction, $type, $round);
    }
}
