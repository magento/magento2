<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\View\Design\Theme\ThemePackage;

/**
 * Factory for theme packages
 */
class ThemePackageFactory
{
    /**
     * Create an instance of ThemePackage
     *
     * @param string $key
     * @param string $path
     *
     * @return ThemePackage
     */
    public function create($key, $path)
    {
        return new ThemePackage($key, $path);
    }
}
