<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Intl;

/**
 * Class NumberFormatterFactory
 * @package Magento\Framework
 */
class NumberFormatterFactory
{
    /**
     * Factory method for \NumberFormatter
     *
     * @param string $locale
     * @param int $style
     * @param string $pattern
     * @return \NumberFormatter
     */
    public function create($locale, $style, $pattern = null)
    {
        return new \NumberFormatter($locale, $style, $pattern);
    }
}
