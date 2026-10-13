<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Validator;

use Magento\Framework\Setup\Lists;

/**
 * Currency validator model
 *
 * @api
 */
class Currency
{
    /**
     * @var Lists
     */
    protected $lists;

    /**
     * Constructor
     *
     * @param Lists $lists
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
