<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Filter;

/**
 * Url compatible translit filter
 *
 * Process string based on convertation table
 */
class TranslitUrl extends Translit
{
    /**
     * Filter value
     *
     * @param string $string
     * @return string
     */
    public function filter($string)
    {
        $string = preg_replace('#[^0-9a-z]+#i', '-', parent::filter($string));
        $string = strtolower($string);
        $string = trim($string, '-');

        return $string;
    }
}
