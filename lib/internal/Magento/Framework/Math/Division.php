<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Math;

/**
 * Division library
 *
 * @api
 * @since 100.0.2
 */
class Division
{
    /**
     * Const for correct dividing decimal values
     */
    const DIVIDE_EPSILON = 10000;

    /**
     * Returns the floating point remainder (modulo) of the division of the arguments
     *
     * @param float|int $dividend
     * @param float|int $divisor
     * @return float|int
     */
    public function getExactDivision($dividend, $divisor)
    {
        $epsilon = $divisor / self::DIVIDE_EPSILON;

        $remainder = fmod($dividend, $divisor);
        if (abs($remainder - $divisor) < $epsilon || abs($remainder) < $epsilon) {
            $remainder = 0;
        }

        return $remainder;
    }
}
