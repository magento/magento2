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
 * @since 2.0.0
 */
interface ResolverInterface
{
    /**
     * Return path to default locale
     *
     * @return string
     * @since 2.0.0
     */
    public function getDefaultLocalePath();

    /**
     * Set default locale code
     *
     * @param   string $locale
     * @return  self
     * @since 2.0.0
     */
    public function setDefaultLocale($locale);

    /**
     * Retrieve default locale code
     *
     * @return string
     * @since 2.0.0
     */
    public function getDefaultLocale();

    /**
     * Set locale
     *
     * @param   string $locale
     * @return  self
     * @since 2.0.0
     */
    public function setLocale($locale = null);

    /**
     * Retrieve locale
     *
     * @return string
     * @since 2.0.0
     */
    public function getLocale();

    /**
     * Push current locale to stack and replace with locale from specified scope
     *
     * @param int $scopeId
     * @return string|null
     * @since 2.0.0
     */
    public function emulate($scopeId);

    /**
     * Get last locale, used before last emulation
     *
     * @return string|null
     * @since 2.0.0
     */
    public function revert();
}
