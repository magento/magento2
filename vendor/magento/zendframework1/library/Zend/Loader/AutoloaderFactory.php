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

#require_once dirname(__FILE__) . '/SplAutoloader.php';

if (class_exists('Zend_Loader_AutoloaderFactory')) return;

/**
 * @package    Zend_Loader
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Loader_AutoloaderFactory
{
    const STANDARD_AUTOLOADER  = 'Zend_Loader_StandardAutoloader';
    const CLASS_MAP_AUTOLOADER = 'Zend_Loader_ClassMapAutoloader';

    /**
     * @var array All autoloaders registered using the factory
     */
    protected static $loaders = array();

    /**
     * @var Zend_Loader_StandardAutoloader StandardAutoloader instance for resolving
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
     * @param  array|Traversable $options (optional) options to use. Defaults to Zend_Loader_StandardAutoloader
     * @return void
     * @throws Zend_Loader_Exception_InvalidArgumentException for invalid options
     * @throws Zend_Loader_Exception_InvalidArgumentException for unloadable autoloader classes
     */
    public static function factory($options = null)
    {
        if (null === $options) {
            if (!isset(self::$loaders[self::STANDARD_AUTOLOADER])) {
                $autoloader = self::getStandardAutoloader();
                $autoloader->register();
                self::$loaders[self::STANDARD_AUTOLOADER] = $autoloader;
            }

            // Return so we don't hit the next check's exception (we're done here anyway)
            return;
        }

        if (!is_array($options) && !($options instanceof Traversable)) {
            #require_once 'Exception/InvalidArgumentException.php';
            throw new Zend_Loader_Exception_InvalidArgumentException(
                'Options provided must be an array or Traversable'
            );
        }

        foreach ($options as $class => $options) {
            if (!isset(self::$loaders[$class])) {
                // Check class map autoloader
                if ($class == self::CLASS_MAP_AUTOLOADER) {
                    if (!class_exists(self::CLASS_MAP_AUTOLOADER)) {
                        // Extract the filename from the classname
                        $classMapLoader = substr(
                            strrchr(self::CLASS_MAP_AUTOLOADER, '_'), 1
                        );

                        require_once dirname(__FILE__) . "/$classMapLoader.php";
                    }
                }

                // Autoload with standard autoloader
                $autoloader = self::getStandardAutoloader();
                if (!class_exists($class) && !$autoloader->autoload($class)) {
                    #require_once 'Exception/InvalidArgumentException.php';
                    throw new Zend_Loader_Exception_InvalidArgumentException(sprintf(
                        'Autoloader class "%s" not loaded',
                        $class
                    ));
                }

                // unfortunately is_subclass_of is broken on some 5.3 versions
                // additionally instanceof is also broken for this use case
                if (version_compare(PHP_VERSION, '5.3.7', '>=')) {
                        if (!is_subclass_of($class, 'Zend_Loader_SplAutoloader')) {
                        #require_once 'Exception/InvalidArgumentException.php';
                        throw new Zend_Loader_Exception_InvalidArgumentException(sprintf(
                            'Autoloader class %s must implement Zend\\Loader\\SplAutoloader',
                            $class
                        ));
                    }
                }

                if ($class === self::STANDARD_AUTOLOADER) {
                    $autoloader->setOptions($options);
                } else {
                    $autoloader = new $class($options);
                }
                $autoloader->register();
                self::$loaders[$class] = $autoloader;
            } else {
                self::$loaders[$class]->setOptions($options);
            }
        }
    }

    /**
     * Get an list of all autoloaders registered with the factory
     *
     * Returns an array of autoloader instances.
     *
     * @return array
     */
    public static function getRegisteredAutoloaders()
    {
        return self::$loaders;
    }

    /**
     * Retrieves an autoloader by class name
     *
     * @param string $class
     * @return Zend_Loader_SplAutoloader
     * @throws Zend_Loader_Exception_InvalidArgumentException for non-registered class
     */
    public static function getRegisteredAutoloader($class)
    {
        if (!isset(self::$loaders[$class])) {
            #require_once 'Exception/InvalidArgumentException.php';
            throw new Zend_Loader_Exception_InvalidArgumentException(sprintf('Autoloader class "%s" not loaded', $class));
        }
        return self::$loaders[$class];
    }

    /**
     * Unregisters all autoloaders that have been registered via the factory.
     * This will NOT unregister autoloaders registered outside of the fctory.
     *
     * @return void
     */
    public static function unregisterAutoloaders()
    {
        foreach (self::getRegisteredAutoloaders() as $class => $autoloader) {
            spl_autoload_unregister(array($autoloader, 'autoload'));
            unset(self::$loaders[$class]);
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
        if (!isset(self::$loaders[$autoloaderClass])) {
            return false;
        }

        $autoloader = self::$loaders[$autoloaderClass];
        spl_autoload_unregister(array($autoloader, 'autoload'));
        unset(self::$loaders[$autoloaderClass]);
        return true;
    }

    /**
     * Get an instance of the standard autoloader
     *
     * Used to attempt to resolve autoloader classes, using the
     * StandardAutoloader. The instance is marked as a fallback autoloader, to
     * allow resolving autoloaders not under the "Zend" or "Zend" namespaces.
     *
     * @return Zend_Loader_SplAutoloader
     */
    protected static function getStandardAutoloader()
    {
        if (null !== self::$standardAutoloader) {
            return self::$standardAutoloader;
        }

        // Extract the filename from the classname
        $stdAutoloader = substr(strrchr(self::STANDARD_AUTOLOADER, '_'), 1);

        if (!class_exists(self::STANDARD_AUTOLOADER)) {
            #require_once dirname(__FILE__) . "/$stdAutoloader.php";
        }
        $loader = new Zend_Loader_StandardAutoloader();
        self::$standardAutoloader = $loader;
        return self::$standardAutoloader;
    }
}
