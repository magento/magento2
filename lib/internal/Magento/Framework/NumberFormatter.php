<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework;

class NumberFormatter extends \NumberFormatter
{
    /**
     * Creates a currency instance.
     *
     * @param null $locale Locale name
     * @param null $style
     * @param null $pattern
     */
    public function __construct(
        $locale = null,
        $style = \NumberFormatter::CURRENCY,
        $pattern = null
    ) {
        parent::__construct($locale, $style, $pattern);
    }
}
