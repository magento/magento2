<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme list interface
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
