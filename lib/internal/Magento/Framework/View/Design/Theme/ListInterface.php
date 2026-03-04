<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme list interface
 *
 * @api
 * @since 100.0.2
 */
interface ListInterface
{
    /**
     * Get theme by area and theme_path
     *
     * @param string $fullPath
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function getThemeByFullPath($fullPath);
}
