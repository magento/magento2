<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator;

use Magento\Setup\Model\Lists;

/**
 * Locale validator model
 */
class locale
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
     * Validate locale code
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
