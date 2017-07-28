<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator;

use Magento\Framework\Setup\Lists;

/**
 * Currency validator model
 * @since 2.0.0
 */
class Currency
{
    /**
     * @var Lists
     * @since 2.0.0
     */
    protected $lists;

    /**
     * Constructor
     *
     * @param Lists $lists
     * @since 2.0.0
     */
    public function __construct(Lists $lists)
    {
        $this->lists = $lists;
    }

    /**
     * Validate currency code
     *
     * @param string $currencyCode
     * @return bool
     * @api
     * @since 2.0.0
     */
    public function isValid($currencyCode)
    {
        $isValid = true;
        $allowedCurrencyCodes = array_keys($this->lists->getCurrencyList());

        if (!$currencyCode || !in_array($currencyCode, $allowedCurrencyCodes)) {
            $isValid = false;
        }

        return $isValid;
    }
}
