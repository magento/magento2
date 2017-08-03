<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Design Interface
 * @since 2.0.0
 */
interface DesignInterface
{
    /**
     * Default design area
     */
    const DEFAULT_AREA = 'frontend';

    /**#@+
     * Public directories prefix group
     */
    const PUBLIC_VIEW_DIR   = '_view';
    const PUBLIC_THEME_DIR  = '_theme';
    /**#@-*/

    /**
     * Common node path to theme design configuration
     */
    const XML_PATH_THEME_ID = 'design/theme/theme_id';

    /**
     * Set package area
     *
     * @param string $area
     * @return DesignInterface
     * @TODO MAGETWO-31474: Remove deprecated method setArea
     * @since 2.0.0
     */
    public function setArea($area);

    /**
     * Retrieve package area
     *
     * @return string
     * @since 2.0.0
     */
    public function getArea();

    /**
     * Set theme path
     *
     * @param Design\ThemeInterface|int|string $theme
     * @param string|null $area
     * @return DesignInterface
     * @since 2.0.0
     */
    public function setDesignTheme($theme, $area = null);

    /**
     * Get default theme which declared in configuration
     *
     * @param string|null $area
     * @param array $params
     * @return string
     * @since 2.0.0
     */
    public function getConfigurationDesignTheme($area = null, array $params = []);

    /**
     * Set default design theme
     *
     * @return DesignInterface
     * @since 2.0.0
     */
    public function setDefaultDesignTheme();

    /**
     * Design theme model getter
     *
     * @return Design\ThemeInterface
     * @since 2.0.0
     */
    public function getDesignTheme();

    /**
     * Convert theme model into a theme path literal
     *
     * @param Design\ThemeInterface $theme
     * @return string
     * @since 2.0.0
     */
    public function getThemePath(Design\ThemeInterface $theme);

    /**
     * Get locale
     *
     * @return string
     * @since 2.0.0
     */
    public function getLocale();

    /**
     * Get design settings for current request
     *
     * @return array
     * @since 2.0.0
     */
    public function getDesignParams();
}
