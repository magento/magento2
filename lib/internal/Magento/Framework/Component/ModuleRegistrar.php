<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

/**
 * Provides ability to statically register modules
 *
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class ModuleRegistrar implements ComponentRegistryInterface
{
    /**
     * Paths to modules
     *
     * @var string[]
     */
    private static $modulePaths = [];

    /**
     * Sets the location of a module.
     *
     * @param string $moduleName Fully-qualified module name
     * @param string $path Absolute file path to the module
     * @return void
     */
    public static function register($moduleName, $path)
    {
        self::$modulePaths[$moduleName] = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        return self::$modulePaths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($moduleName)
    {
        return isset(self::$modulePaths[$moduleName]) ? self::$modulePaths[$moduleName] : null;
    }
}
