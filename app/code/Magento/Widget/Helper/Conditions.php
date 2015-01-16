<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
