<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Url compatible translit filter
 *
 * Process string based on convertation table
 * @since 2.0.0
 */
class TranslitUrl extends Translit
{
    /**
     * Filter value
     *
     * @param string $string
     * @return string
     * @since 2.0.0
     */
    public function filter($string)
    {
        $string = preg_replace('#[^0-9a-z]+#i', '-', parent::filter($string));
        $string = strtolower($string);
        $string = trim($string, '-');

        return $string;
    }
}
