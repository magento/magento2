<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Interface ThemeProviderInterface
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
