<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation;


class RowBaseCalculator extends AbstractAggregateCalculator
{
    /**
     * {@inheritdoc}
     */
    protected function roundAmount(
        $amount,
        $rate = null,
        $direction = null,
        $type = self::KEY_REGULAR_DELTA_ROUNDING,
        $round = true,
        $item = null
    ) {

        if ($item->getAssociatedItemCode()) {
            // Use delta rounding of the product's instead of the weee's
            $type = $type . $item->getAssociatedItemCode();
        } else {
            $type = $type . $item->getCode();
        }

        return $this->deltaRound($amount, $rate, $direction, $type, $round);
    }
}
