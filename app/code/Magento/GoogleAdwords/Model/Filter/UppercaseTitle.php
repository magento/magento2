<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\GoogleAdwords\Model\Filter;

use Laminas\Filter\FilterInterface;

/**
 * @api
 * @since 100.0.2
 */
class UppercaseTitle implements FilterInterface
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
