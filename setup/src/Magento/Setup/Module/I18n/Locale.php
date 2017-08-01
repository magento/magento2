<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n;

/**
 *  Locale
 * @since 2.0.0
 */
class Locale
{
    /**
     * Default system locale
     */
    const DEFAULT_SYSTEM_LOCALE = 'en_US';

    /**
     * Locale name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_locale;

    /**
     * Locale construct
     *
     * @param string $locale
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function __construct($locale)
    {
        if (!preg_match('/[a-z]{2}_[A-Z]{2}/', $locale)) {
            throw new \InvalidArgumentException('Target locale must match the following format: "aa_AA".');
        }
        $this->_locale = $locale;
    }

    /**
     * Return locale string
     *
     * @return string
     * @since 2.0.0
     */
    public function __toString()
    {
        return $this->_locale;
    }
}
