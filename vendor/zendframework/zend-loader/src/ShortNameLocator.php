<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Loader;

/**
 * Short name locator interface
 */
interface ShortNameLocator
{
    /**
     * Whether or not a Helper by a specific name
     *
     * @param  string $name
     * @return bool
     */
    public function isLoaded($name);

    /**
     * Return full class name for a named helper
     *
     * @param  string $name
     * @return string
     */
    public function getClassName($name);

    /**
     * Load a helper via the name provided
     *
     * @param  string $name
     * @return string
     */
    public function load($name);
}
