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
 * @category    Magento
 * @package     Magento_ObjectManager
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

interface Magento_ObjectManager
{
    /**
     * Create new object instance
     *
     * @param string $className
     * @param array $arguments
     * @param bool $isShared
     * @return mixed
     */
    public function create($className, array $arguments = array(), $isShared = true);

    /**
     * Retrieve cached object instance
     *
     * @param string $className
     * @param array $arguments
     * @return mixed
     */
    public function get($className, array $arguments = array());

    /**
     * Set DI configuration
     *
     * @param array $configuration
     * @return Magento_ObjectManager
     */
    public function setConfiguration(array $configuration = array());

    /**
     * Add shared instance
     *
     * @param object $instance
     * @param string $classOrAlias
     * @return Magento_ObjectManager
     */
    public function addSharedInstance($instance, $classOrAlias);

    /**
     * Remove shared instance
     *
     * @param string $classOrAlias
     * @return Magento_ObjectManager
     */
    public function removeSharedInstance($classOrAlias);

    /**
     * Check whether object manager has shared instance of given class (alias)
     *
     * @param string $classOrAlias
     * @return bool
     */
    public function hasSharedInstance($classOrAlias);

    /**
     * Add alias
     *
     * @param  string $alias
     * @param  string $class
     * @param  array  $parameters
     * @return Magento_ObjectManager
     * @throws Zend\Di\Exception\InvalidArgumentException
     */
    public function addAlias($alias, $class, array $parameters = array());

    /**
     * Get class name by alias
     *
     * @param string
     * @return string|bool
     */
    public function getClassFromAlias($alias);
}
