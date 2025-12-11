<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Interface ThemeProviderInterface
 *
 * @api
 */
interface ThemeProviderInterface
{
    /**
     * Get theme from DB by area and theme_path
     *
     * @param string $fullPath
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function getThemeByFullPath($fullPath);

    /**
     * Filter theme customization
     *
     * @param string $area
     * @param int $type
     * @return array
     */
    public function getThemeCustomizations($area, $type = \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL);

    /**
     * Get theme by id
     *
     * @param int $themeId
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function getThemeById($themeId);
}
