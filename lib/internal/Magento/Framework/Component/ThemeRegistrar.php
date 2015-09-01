<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register themes
 */
class ThemeRegistrar implements ComponentRegistryInterface
{
    /**
     * Paths to themes
     *
     * @var string[]
     */
    private static $themePaths = [];

    /**
     * Sets the location of a themes.
     *
     * @param string $themeName Theme name
     * @param string $path Absolute file path to the module
     * @return void
     */
    public static function register($themeName, $path)
    {
        self::$themePaths[$themeName] = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        return self::$themePaths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($themeName)
    {
        return isset(self::$themePaths[$themeName]) ? self::$themePaths[$themeName] : null;
    }
}
