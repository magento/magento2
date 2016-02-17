<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di;

interface DependencyInjectionInterface extends LocatorInterface
{
    /**
     * Retrieve a new instance of a class
     *
     * Forces retrieval of a discrete instance of the given class, using the
     * constructor parameters provided.
     *
     * @param  mixed       $name   Class name or service alias
     * @param  array       $params Parameters to pass to the constructor
     * @return object|null
     */
    public function newInstance($name, array $params = array());
}
