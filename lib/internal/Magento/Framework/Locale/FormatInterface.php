<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * @api
 * @since 2.0.0
 */
interface FormatInterface
{
    /**
     * Returns the first found number from a string
     * Parsing depends on given locale (grouping and decimal)
     *
     * Examples for input:
     * '  2345.4356,1234' = 23455456.1234
     * '+23,3452.123' = 233452.123
     * ' 12343 ' = 12343
     * '-9456km' = -9456
     * '0' = 0
     * '2 054,10' = 2054.1
     * '2'054.52' = 2054.52
     * '2,46 GB' = 2.46
     *
     * @param string|float|int $value
     * @return float|null
     * @since 2.0.0
     */
    public function getNumber($value);

    /**
     * Returns an array with price formatting info for js function
     * formatCurrency in js/varien/js.js
     *
     * @return array
     * @since 2.0.0
     */
    public function getPriceFormat();
}
