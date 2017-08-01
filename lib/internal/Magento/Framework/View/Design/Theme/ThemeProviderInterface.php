<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Interface ThemeProviderInterface
 * @since 2.0.0
 */
interface ThemeProviderInterface
{
    /**
     * Get theme from DB by area and theme_path
     *
     * @param string $fullPath
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    public function getThemeByFullPath($fullPath);

    /**
     * Filter theme customization
     *
     * @param string $area
     * @param int $type
     * @return array
     * @since 2.0.0
     */
    public function getThemeCustomizations($area, $type = \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL);

    /**
     * Get theme by id
     *
     * @param int $themeId
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    public function getThemeById($themeId);
}
