<?php
/**
 * Filter to uppercase the first character of each word in a string
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Model\Filter;

class UppercaseTitle implements \Zend_Filter_Interface
{
    /**
     * Convert title to uppercase
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if (function_exists('mb_convert_case')) {
            $value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
        } else {
            $value = ucwords($value);
        }
        return $value;
    }
}
