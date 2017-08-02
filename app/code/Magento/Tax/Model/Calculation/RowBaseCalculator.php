<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation;

/**
 * Class \Magento\Tax\Model\Calculation\RowBaseCalculator
 *
 * @since 2.0.0
 */
class RowBaseCalculator extends AbstractAggregateCalculator
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
        if ($item->getAssociatedItemCode()) {
            // Use delta rounding of the product's instead of the weee's
            $type = $type . $item->getAssociatedItemCode();
        } else {
            $type = $type . $item->getCode();
        }

        return $this->deltaRound($amount, $rate, $direction, $type, $round);
    }
}
