<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * Manages locale config information
 *
 * @api
 */
interface ResolverInterface
{
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
     * @return  self
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
     * @return  self
     */
    public function setLocale($locale = null);

    /**
     * Retrieve locale
     *
     * @return string
     */
    public function getLocale();

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
