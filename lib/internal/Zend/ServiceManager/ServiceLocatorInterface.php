<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_ServiceManager
 */

namespace Zend\ServiceManager;

/**
 * Service locator interface
 *
 * @category Zend
 * @package Zend_ServiceManager
 */
interface ServiceLocatorInterface
{
    /**
     * Retrieve a registered instance
     *
     * @param  string  $name
     * @throws Exception\ServiceNotFoundException
     * @return object|array
     */
    public function get($name);

    /**
     * Check for a registered instance
     *
     * @param  string|array  $name
     * @return bool
     */
    public function has($name);
}
