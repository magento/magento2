<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Provides ability to statically register modules which do not reside in the modules directory. Not all modules
 * will be registered by default.
 *
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class Registrar implements ModuleRegistryInterface
{
    /**
     * Paths to modules
     *
     * @var string[]
     */
    private static $modulePaths = [];

    /**
     * Sets the location of a module. Necessary for modules which do not reside in modules directory
     *
     * @param string $moduleName Fully-qualified module name
     * @param string $path Absolute file path to the module
     * @return void
     */
    public static function registerModule($moduleName, $path)
    {
        self::$modulePaths[$moduleName] = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getModulePaths()
    {
        return self::$modulePaths;
    }

    /**
     * {@inheritdoc}
     */
    public function getModulePath($moduleName)
    {
        return isset(self::$modulePaths[$moduleName]) ? self::$modulePaths[$moduleName] : null;
    }
}
