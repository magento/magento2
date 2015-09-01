<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register libraries
 */
class LibraryRegistrar implements ComponentRegistryInterface
{
    /**
     * Paths to libraries
     *
     * @var string[]
     */
    private static $libraryPaths = [];

    /**
     * Sets the location of a libraries
     *
     * @param string $libraryName library name
     * @param string $path Absolute file path to the module
     * @return void
     */
    public static function register($libraryName, $path)
    {
        self::$libraryPaths[$libraryName] = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        return self::$libraryPaths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($libraryName)
    {
        return isset(self::$libraryPaths[$libraryName]) ? self::$libraryPaths[$libraryName] : null;
    }
}
