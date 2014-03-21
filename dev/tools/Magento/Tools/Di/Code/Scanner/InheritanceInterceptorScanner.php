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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Di\Code\Scanner;

class InheritanceInterceptorScanner implements ScannerInterface
{
    /**
     * Get intercepted class names
     *
     * @param array $classes
     * @param array $interceptedEntities
     * @return array
     */
    public function collectEntities(array $classes, array $interceptedEntities = array())
    {
        $output = array();
        foreach ($classes as $class) {
            foreach ($interceptedEntities as $interceptorClass) {
                $interceptedEntity = substr($interceptorClass, 0, -12);
                if (is_subclass_of($class, $interceptedEntity)) {
                    $reflectionClass = new \ReflectionClass($class);
                    if (!$reflectionClass->isAbstract() && !$reflectionClass->isFinal()) {
                        $output[] = $class . '\\Interceptor';
                    }
                }
            }
        }
        $output = array_merge($interceptedEntities, $output);
        $output = array_unique($output);
        return $output;
    }
}
