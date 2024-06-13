<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Validator;

use Magento\Framework\Setup\Lists;

/**
 * Locale validator model
 *
 * @api
 */
class Locale
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
     * Validate locale code. Code must be in the list of allowed locales.
     *
     * @param string $localeCode
     * @return bool
     */
    public function isValid($localeCode)
    {
        $isValid = true;
        $allowedLocaleCodes = array_keys($this->lists->getLocaleList());

        if (!$localeCode || !in_array($localeCode, $allowedLocaleCodes)) {
            $isValid = false;
        }

        return $isValid;
    }
}
