<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\View\Design\Theme\ThemePackage;

/**
 * Factory for theme packages
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($key, $path)
    {
        return new ThemePackage($key, $path);
    }
}
