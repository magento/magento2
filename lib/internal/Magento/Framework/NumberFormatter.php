<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
