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
 * @package    Zend_Loader
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

if (interface_exists('Zend_Loader_SplAutoloader')) return;

/**
 * Defines an interface for classes that may register with the spl_autoload 
 * registry
 *
 * @package    Zend_Loader
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Loader_SplAutoloader
{
    /**
     * Constructor
     *
     * Allow configuration of the autoloader via the constructor.
     * 
     * @param  null|array|Traversable $options 
     * @return void
     */
    public function __construct($options = null);

    /**
     * Configure the autoloader
     *
     * In most cases, $options should be either an associative array or 
     * Traversable object.
     * 
     * @param  array|Traversable $options 
     * @return SplAutoloader
     */
    public function setOptions($options);

    /**
     * Autoload a class
     *
     * @param   $class
     * @return  mixed
     *          False [if unable to load $class]
     *          get_class($class) [if $class is successfully loaded]
     */
    public function autoload($class);

    /**
     * Register the autoloader with spl_autoload registry
     *
     * Typically, the body of this will simply be:
     * <code>
     * spl_autoload_register(array($this, 'autoload'));
     * </code>
     * 
     * @return void
     */
    public function register();
}
