<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di;

interface ServiceLocatorInterface extends LocatorInterface
{
    /**
     * Register a service with the locator
     *
     * @abstract
     * @param  string                  $name
     * @param  mixed                   $service
     * @return ServiceLocatorInterface
     */
    public function set($name, $service);
}
