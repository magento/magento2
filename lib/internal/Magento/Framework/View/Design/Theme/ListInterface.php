<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme list interface
 *
 * @api
 * @since 2.0.0
 */
interface ListInterface
{
    /**
     * Get theme by area and theme_path
     *
     * @param string $fullPath
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    public function getThemeByFullPath($fullPath);
}
