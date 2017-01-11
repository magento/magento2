<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
