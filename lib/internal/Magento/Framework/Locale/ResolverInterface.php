<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

interface ResolverInterface
{
    /**
     * Default locale
     */
    const DEFAULT_LOCALE = 'en_US';

    /**
     * Return path to default locale
     *
     * @return string
     */
    public function getDefaultLocalePath();

    /**
     * Set default locale code
     *
     * @param   string $locale
     * @return  \Magento\Framework\Locale\ResolverInterface
     */
    public function setDefaultLocale($locale);

    /**
     * Retrieve default locale code
     *
     * @return string
     */
    public function getDefaultLocale();

    /**
     * Set locale
     *
     * @param   string $locale
     * @return  \Magento\Framework\Locale\ResolverInterface
     */
    public function setLocale($locale = null);

    /**
     * Retrieve locale object
     *
     * @return \Magento\Framework\LocaleInterface
     */
    public function getLocale();

    /**
     * Retrieve locale code
     *
     * @return string
     */
    public function getLocaleCode();

    /**
     * Specify current locale code
     *
     * @param   string $code
     * @return  \Magento\Framework\Locale\ResolverInterface
     */
    public function setLocaleCode($code);

    /**
     * Push current locale to stack and replace with locale from specified scope
     *
     * @param int $scopeId
     * @return string|null
     */
    public function emulate($scopeId);

    /**
     * Get last locale, used before last emulation
     *
     * @return string|null
     */
    public function revert();
}
