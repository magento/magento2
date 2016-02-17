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

if (class_exists('Zend\Loader\AutoloaderFactory')) {
    return;
}

abstract class AutoloaderFactory
{
    const STANDARD_AUTOLOADER = 'Zend\Loader\StandardAutoloader';

    /**
     * @var array All autoloaders registered using the factory
     */
    protected static $loaders = array();

    /**
     * @var StandardAutoloader StandardAutoloader instance for resolving
     * autoloader classes via the include_path
     */
    protected static $standardAutoloader;

    /**
     * Factory for autoloaders
     *
     * Options should be an array or Traversable object of the following structure:
     * <code>
     * array(
     *     '<autoloader class name>' => $autoloaderOptions,
     * )
     * </code>
     *
     * The factory will then loop through and instantiate each autoloader with
     * the specified options, and register each with the spl_autoloader.
     *
     * You may retrieve the concrete autoloader instances later using
     * {@link getRegisteredAutoloaders()}.
     *
     * Note that the class names must be resolvable on the include_path or via
     * the Zend library, using PSR-0 rules (unless the class has already been
     * loaded).
     *
     * @param  array|Traversable $options (optional) options to use. Defaults to Zend\Loader\StandardAutoloader
     * @return void
     * @throws Exception\InvalidArgumentException for invalid options
     * @throws Exception\InvalidArgumentException for unloadable autoloader classes
     * @throws Exception\DomainException for autoloader classes not implementing SplAutoloader
     */
    public static function factory($options = null)
    {
        if (null === $options) {
            if (!isset(static::$loaders[static::STANDARD_AUTOLOADER])) {
                $autoloader = static::getStandardAutoloader();
                $autoloader->register();
                static::$loaders[static::STANDARD_AUTOLOADER] = $autoloader;
            }

            // Return so we don't hit the next check's exception (we're done here anyway)
            return;
        }

        if (!is_array($options) && !($options instanceof Traversable)) {
            require_once __DIR__ . '/Exception/InvalidArgumentException.php';
            throw new Exception\InvalidArgumentException(
                'Options provided must be an array or Traversable'
            );
        }

        foreach ($options as $class => $autoloaderOptions) {
            if (!isset(static::$loaders[$class])) {
                $autoloader = static::getStandardAutoloader();
                if (!class_exists($class) && !$autoloader->autoload($class)) {
                    require_once 'Exception/InvalidArgumentException.php';
                    throw new Exception\InvalidArgumentException(
                        sprintf('Autoloader class "%s" not loaded', $class)
                    );
                }

                if (!is_subclass_of($class, 'Zend\Loader\SplAutoloader')) {
                    require_once 'Exception/InvalidArgumentException.php';
                    throw new Exception\InvalidArgumentException(
                        sprintf('Autoloader class %s must implement Zend\\Loader\\SplAutoloader', $class)
                    );
                }

                if ($class === static::STANDARD_AUTOLOADER) {
                    $autoloader->setOptions($autoloaderOptions);
                } else {
                    $autoloader = new $class($autoloaderOptions);
                }
                $autoloader->register();
                static::$loaders[$class] = $autoloader;
            } else {
                static::$loaders[$class]->setOptions($autoloaderOptions);
            }
        }
    }

    /**
     * Get a list of all autoloaders registered with the factory
     *
     * Returns an array of autoloader instances.
     *
     * @return array
     */
    public static function getRegisteredAutoloaders()
    {
        return static::$loaders;
    }

    /**
     * Retrieves an autoloader by class name
     *
     * @param  string $class
     * @return SplAutoloader
     * @throws Exception\InvalidArgumentException for non-registered class
     */
    public static function getRegisteredAutoloader($class)
    {
        if (!isset(static::$loaders[$class])) {
            require_once 'Exception/InvalidArgumentException.php';
            throw new Exception\InvalidArgumentException(sprintf('Autoloader class "%s" not loaded', $class));
        }
        return static::$loaders[$class];
    }

    /**
     * Unregisters all autoloaders that have been registered via the factory.
     * This will NOT unregister autoloaders registered outside of the fctory.
     *
     * @return void
     */
    public static function unregisterAutoloaders()
    {
        foreach (static::getRegisteredAutoloaders() as $class => $autoloader) {
            spl_autoload_unregister(array($autoloader, 'autoload'));
            unset(static::$loaders[$class]);
        }
    }

    /**
     * Unregister a single autoloader by class name
     *
     * @param  string $autoloaderClass
     * @return bool
     */
    public static function unregisterAutoloader($autoloaderClass)
    {
        if (!isset(static::$loaders[$autoloaderClass])) {
            return false;
        }

        $autoloader = static::$loaders[$autoloaderClass];
        spl_autoload_unregister(array($autoloader, 'autoload'));
        unset(static::$loaders[$autoloaderClass]);
        return true;
    }

    /**
     * Get an instance of the standard autoloader
     *
     * Used to attempt to resolve autoloader classes, using the
     * StandardAutoloader. The instance is marked as a fallback autoloader, to
     * allow resolving autoloaders not under the "Zend" namespace.
     *
     * @return SplAutoloader
     */
    protected static function getStandardAutoloader()
    {
        if (null !== static::$standardAutoloader) {
            return static::$standardAutoloader;
        }


        if (!class_exists(static::STANDARD_AUTOLOADER)) {
            // Extract the filename from the classname
            $stdAutoloader = substr(strrchr(static::STANDARD_AUTOLOADER, '\\'), 1);
            require_once __DIR__ . "/$stdAutoloader.php";
        }
        $loader = new StandardAutoloader();
        static::$standardAutoloader = $loader;
        return static::$standardAutoloader;
    }

    /**
     * Checks if the object has this class as one of its parents
     *
     * @see https://bugs.php.net/bug.php?id=53727
     * @see https://github.com/zendframework/zf2/pull/1807
     *
     * @deprecated since zf 2.3 requires PHP >= 5.3.23
     *
     * @param  string $className
     * @param  string $type
     * @return bool
     */
    protected static function isSubclassOf($className, $type)
    {
        return is_subclass_of($className, $type);
    }
}
