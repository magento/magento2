<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Generator;

use Magento\Framework\Autoload\AutoloaderRegistry;

/**
 * DefinedClasses class detects if a class has been defined
 */
class DefinedClasses
{
    /**
     * Determine if a class can be loaded without using Code\Generator\Autoloader.
     *
     * @param string $className
     * @return bool
     */
    public function isClassLoadable($className)
    {
        return $this->isClassLoadableFromMemory($className) || $this->isClassLoadableFromDisc($className);
    }

    /**
     * Determine if a class exists in memory
     *
     * @param string $className
     * @return bool
     */
    public function isClassLoadableFromMemory($className)
    {
        return class_exists($className, false) || interface_exists($className, false);
    }

    /**
     * Determine if a class exists on disc
     *
     * @param string $className
     * @return bool
     */
    public function isClassLoadableFromDisc($className)
    {
        try {
            return (bool)AutoloaderRegistry::getAutoloader()->findFile($className);
        } catch (\Exception $e) {
            // Couldn't get access to the autoloader so we need to allow class_exists to call autoloader chain
            return (class_exists($className) || interface_exists($className));
        }
    }
}
