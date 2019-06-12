<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        return $this->isClassLoadableFromMemory($className) || $this->isClassLoadableFromDisk($className);
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
     * Determine if a class exists on disk
     *
     * @param string $className
     * @return bool
     * @deprecated
     */
    public function isClassLoadableFromDisc($className)
    {
        return $this->isClassLoadableFromDisk($className);
    }

    /**
     * Determine if a class exists on disk
     *
     * @param string $className
     * @return bool
     */
    public function isClassLoadableFromDisk($className)
    {
        try {
            return (bool)AutoloaderRegistry::getAutoloader()->findFile($className);
        } catch (\Exception $e) {
            // Couldn't get access to the autoloader so we need to allow class_exists to call autoloader chain
            return (class_exists($className) || interface_exists($className));
        }
    }
}
