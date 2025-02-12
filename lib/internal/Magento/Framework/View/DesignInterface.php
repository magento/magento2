<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View;

/**
 * Interface \Magento\Framework\View\DesignInterface
 *
 * @api
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
     */
    public function setArea($area);

    /**
     * Retrieve package area
     *
     * @return string
     */
    public function getArea();

    /**
     * Set theme path
     *
     * @param Design\ThemeInterface|int|string $theme
     * @param string|null $area
     * @return DesignInterface
     */
    public function setDesignTheme($theme, $area = null);

    /**
     * Get default theme which declared in configuration
     *
     * @param string|null $area
     * @param array $params
     * @return string
     */
    public function getConfigurationDesignTheme($area = null, array $params = []);

    /**
     * Set default design theme
     *
     * @return DesignInterface
     */
    public function setDefaultDesignTheme();

    /**
     * Design theme model getter
     *
     * @return Design\ThemeInterface
     */
    public function getDesignTheme();

    /**
     * Convert theme model into a theme path literal
     *
     * @param Design\ThemeInterface $theme
     * @return string
     */
    public function getThemePath(Design\ThemeInterface $theme);

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Get design settings for current request
     *
     * @return array
     */
    public function getDesignParams();
}
