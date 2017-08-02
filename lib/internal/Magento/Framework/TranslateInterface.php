<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * @todo change this interface when i18n-related logic is moved to library
 * @since 2.0.0
 */
interface TranslateInterface
{
    /**
     * Default translation string
     */
    const DEFAULT_STRING = 'Translate String';

    /**
     * Initialize translation data
     *
     * @param string|null $area
     * @param bool $forceReload
     * @return \Magento\Framework\TranslateInterface
     * @since 2.0.0
     */
    public function loadData($area = null, $forceReload = false);

    /**
     * Retrieve translation data
     *
     * @return array
     * @since 2.0.0
     */
    public function getData();

    /**
     * Retrieve locale
     *
     * @return string
     * @since 2.0.0
     */
    public function getLocale();

    /**
     * Set locale
     *
     * @param string $locale
     * @return \Magento\Framework\TranslateInterface
     * @since 2.0.0
     */
    public function setLocale($locale);

    /**
     * Retrieve theme code
     *
     * @return string
     * @since 2.0.0
     */
    public function getTheme();
}
