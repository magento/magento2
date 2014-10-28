<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
