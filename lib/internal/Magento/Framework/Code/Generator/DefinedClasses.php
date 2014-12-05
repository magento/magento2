<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Code\Generator;

use Magento\Framework\Autoload\AutoloaderRegistry;

/**
 * DefinedClasses class detects if a class has been defined
 */
class DefinedClasses
{

    /**
     * Determine if a class can be loaded without using the Code\Generator\Autoloader.
     *
     * @param string $className
     * @return bool
     */
    public function classLoadable($className)
    {
        if ($this->isAlreadyDefined($className)) {
            return true;
        }
        return $this->performAutoload($className);
    }

    /**
     * Checks whether class is already defined
     *
     * @param string $className
     * @return bool
     */
    protected function isAlreadyDefined($className)
    {
        return class_exists($className, false) || interface_exists($className, false);
    }

    /**
     * Performs autoload for given class name
     *
     * @param string $className
     * @return bool
     */
    protected function performAutoload($className)
    {
        try {
            return AutoloaderRegistry::getAutoloader()->loadClass($className);
        } catch (\Exception $e) {
            // Couldn't get access to the autoloader so we need to allow class_exists to call autoloader chain
            return (class_exists($className) || interface_exists($className));
        }
    }
}
