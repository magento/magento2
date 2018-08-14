<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator;

use Magento\Framework\Setup\Lists;

/**
 * Locale validator model
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
     *
     * @api
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
