<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator;

use Magento\Framework\Setup\Lists;

/**
 * Locale validator model
 * @since 2.0.0
 */
class Locale
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
     * Validate locale code. Code must be in the list of allowed locales.
     *
     * @param string $localeCode
     * @return bool
     *
     * @api
     * @since 2.0.0
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
