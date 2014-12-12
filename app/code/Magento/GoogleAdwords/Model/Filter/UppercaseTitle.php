<?php
/**
 * Filter to uppercase the first character of each word in a string
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
