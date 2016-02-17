<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Loader;

use Traversable;

if (interface_exists('Zend\Loader\SplAutoloader')) {
    return;
}

/**
 * Defines an interface for classes that may register with the spl_autoload
 * registry
 */
interface SplAutoloader
{
    /**
     * Constructor
     *
     * Allow configuration of the autoloader via the constructor.
     *
     * @param  null|array|Traversable $options
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
