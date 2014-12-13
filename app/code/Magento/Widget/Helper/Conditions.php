<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Widget\Helper;

/**
 * Widget Conditions helper
 */
class Conditions
{
    /**
     * Encode widget conditions to be used with WYSIWIG
     *
     * @param array $value
     * @return string
     */
    public function encode(array $value)
    {
        $value = str_replace(['{', '}', '"', '\\'], ['[', ']', '`', '|'], serialize($value));
        return $value;
    }

    /**
     * Decode previously encoded widget conditions
     *
     * @param string $value
     * @return array
     */
    public function decode($value)
    {
        $value = str_replace(['[', ']', '`', '|'], ['{', '}', '"', '\\'], $value);
        $value = unserialize($value);
        return $value;
    }
}
