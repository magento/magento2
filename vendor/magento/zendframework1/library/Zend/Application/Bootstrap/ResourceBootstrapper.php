<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Interface for bootstrap classes that utilize resource plugins
 *
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Application_Bootstrap_ResourceBootstrapper
{
    /**
     * Register a resource with the bootstrap
     *
     * @param  string|Zend_Application_Resource_Resource $resource
     * @param  null|array|Zend_Config                     $options
     * @return Zend_Application_Bootstrap_ResourceBootstrapper
     */
    public function registerPluginResource($resource, $options = null);

    /**
     * Unregister a resource from the bootstrap
     *
     * @param  string|Zend_Application_Resource_Resource $resource
     * @return Zend_Application_Bootstrap_ResourceBootstrapper
     */
    public function unregisterPluginResource($resource);

    /**
     * Is the requested resource registered?
     *
     * @param  string $resource
     * @return bool
     */
    public function hasPluginResource($resource);

    /**
     * Retrieve resource
     *
     * @param  string $resource
     * @return Zend_Application_Resource_Resource
     */
    public function getPluginResource($resource);

    /**
     * Get all resources
     *
     * @return array
     */
    public function getPluginResources();

    /**
     * Get just resource names
     *
     * @return array
     */
    public function getPluginResourceNames();

    /**
     * Set plugin loader to use to fetch resources
     *
     * @param  Zend_Loader_PluginLoader_Interface Zend_Loader_PluginLoader
     * @return Zend_Application_Bootstrap_ResourceBootstrapper
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader);

    /**
     * Retrieve plugin loader for resources
     *
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader();
}
