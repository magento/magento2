<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator;

use Magento\Framework\Setup\Lists;

/**
 * Timezone validator model
 * @since 2.0.0
 */
class Timezone
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
     * Validate timezone code
     *
     * @param string $timezoneCode
     * @return bool
     * @api
     * @since 2.0.0
     */
    public function isValid($timezoneCode)
    {
        $isValid = true;
        $allowedTimezoneCodes = array_keys($this->lists->getTimezoneList());

        if (!$timezoneCode || !in_array($timezoneCode, $allowedTimezoneCodes)) {
            $isValid = false;
        }

        return $isValid;
    }
}
