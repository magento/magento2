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

// Grab SplAutoloader interface
#require_once dirname(__FILE__) . '/SplAutoloader.php';

/**
 * PSR-0 compliant autoloader
 *
 * Allows autoloading both namespaced and vendor-prefixed classes. Class
 * lookups are performed on the filesystem. If a class file for the referenced
 * class is not found, a PHP warning will be raised by include().
 *
 * @package    Zend_Loader
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    New BSD {@link http://framework.zend.com/license/new-bsd}
 */
class Zend_Loader_StandardAutoloader implements Zend_Loader_SplAutoloader
{
    const NS_SEPARATOR     = '\\';
    const PREFIX_SEPARATOR = '_';
    const LOAD_NS          = 'namespaces';
    const LOAD_PREFIX      = 'prefixes';
    const ACT_AS_FALLBACK  = 'fallback_autoloader';
    const AUTOREGISTER_ZF  = 'autoregister_zf';

    /**
     * @var array Namespace/directory pairs to search; ZF library added by default
     */
    protected $namespaces = array();

    /**
     * @var array Prefix/directory pairs to search
     */
    protected $prefixes = array();

    /**
     * @var bool Whether or not the autoloader should also act as a fallback autoloader
     */
    protected $fallbackAutoloaderFlag = false;

    /**
     * @var bool
     */
    protected $error;

    /**
     * Constructor
     *
     * @param  null|array|Traversable $options
     * @return void
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Configure autoloader
     *
     * Allows specifying both "namespace" and "prefix" pairs, using the
     * following structure:
     * <code>
     * array(
     *     'namespaces' => array(
     *         'Zend'     => '/path/to/Zend/library',
     *         'Doctrine' => '/path/to/Doctrine/library',
     *     ),
     *     'prefixes' => array(
     *         'Phly_'     => '/path/to/Phly/library',
     *     ),
     *     'fallback_autoloader' => true,
     * )
     * </code>
     *
     * @param  array|Traversable $options
     * @return Zend_Loader_StandardAutoloader
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !($options instanceof Traversable)) {
            #require_once dirname(__FILE__) . '/Exception/InvalidArgumentException.php';
            throw new Zend_Loader_Exception_InvalidArgumentException('Options must be either an array or Traversable');
        }

        foreach ($options as $type => $pairs) {
            switch ($type) {
                case self::AUTOREGISTER_ZF:
                    if ($pairs) {
                        $this->registerPrefix('Zend', dirname(dirname(__FILE__)));
                    }
                    break;
                case self::LOAD_NS:
                    if (is_array($pairs) || $pairs instanceof Traversable) {
                        $this->registerNamespaces($pairs);
                    }
                    break;
                case self::LOAD_PREFIX:
                    if (is_array($pairs) || $pairs instanceof Traversable) {
                        $this->registerPrefixes($pairs);
                    }
                    break;
                case self::ACT_AS_FALLBACK:
                    $this->setFallbackAutoloader($pairs);
                    break;
                default:
                    // ignore
            }
        }
        return $this;
    }

    /**
     * Set flag indicating fallback autoloader status
     *
     * @param  bool $flag
     * @return Zend_Loader_StandardAutoloader
     */
    public function setFallbackAutoloader($flag)
    {
        $this->fallbackAutoloaderFlag = (bool) $flag;
        return $this;
    }

    /**
     * Is this autoloader acting as a fallback autoloader?
     *
     * @return bool
     */
    public function isFallbackAutoloader()
    {
        return $this->fallbackAutoloaderFlag;
    }

    /**
     * Register a namespace/directory pair
     *
     * @param  string $namespace
     * @param  string $directory
     * @return Zend_Loader_StandardAutoloader
     */
    public function registerNamespace($namespace, $directory)
    {
        $namespace = rtrim($namespace, self::NS_SEPARATOR). self::NS_SEPARATOR;
        $this->namespaces[$namespace] = $this->normalizeDirectory($directory);
        return $this;
    }

    /**
     * Register many namespace/directory pairs at once
     *
     * @param  array $namespaces
     * @return Zend_Loader_StandardAutoloader
     */
    public function registerNamespaces($namespaces)
    {
        if (!is_array($namespaces) && !$namespaces instanceof Traversable) {
            #require_once dirname(__FILE__) . '/Exception/InvalidArgumentException.php';
            throw new Zend_Loader_Exception_InvalidArgumentException('Namespace pairs must be either an array or Traversable');
        }

        foreach ($namespaces as $namespace => $directory) {
            $this->registerNamespace($namespace, $directory);
        }
        return $this;
    }

    /**
     * Register a prefix/directory pair
     *
     * @param  string $prefix
     * @param  string $directory
     * @return Zend_Loader_StandardAutoloader
     */
    public function registerPrefix($prefix, $directory)
    {
        $prefix = rtrim($prefix, self::PREFIX_SEPARATOR). self::PREFIX_SEPARATOR;
        $this->prefixes[$prefix] = $this->normalizeDirectory($directory);
        return $this;
    }

    /**
     * Register many namespace/directory pairs at once
     *
     * @param  array $prefixes
     * @return Zend_Loader_StandardAutoloader
     */
    public function registerPrefixes($prefixes)
    {
        if (!is_array($prefixes) && !$prefixes instanceof Traversable) {
            #require_once dirname(__FILE__) . '/Exception/InvalidArgumentException.php';
            throw new Zend_Loader_Exception_InvalidArgumentException('Prefix pairs must be either an array or Traversable');
        }

        foreach ($prefixes as $prefix => $directory) {
            $this->registerPrefix($prefix, $directory);
        }
        return $this;
    }

    /**
     * Defined by Autoloadable; autoload a class
     *
     * @param  string $class
     * @return false|string
     */
    public function autoload($class)
    {
        $isFallback = $this->isFallbackAutoloader();
        if (false !== strpos($class, self::NS_SEPARATOR)) {
            if ($this->loadClass($class, self::LOAD_NS)) {
                return $class;
            } elseif ($isFallback) {
                return $this->loadClass($class, self::ACT_AS_FALLBACK);
            }
            return false;
        }
        if (false !== strpos($class, self::PREFIX_SEPARATOR)) {
            if ($this->loadClass($class, self::LOAD_PREFIX)) {
                return $class;
            } elseif ($isFallback) {
                return $this->loadClass($class, self::ACT_AS_FALLBACK);
            }
            return false;
        }
        if ($isFallback) {
            return $this->loadClass($class, self::ACT_AS_FALLBACK);
        }
        return false;
    }

    /**
     * Register the autoloader with spl_autoload
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Error handler
     *
     * Used by {@link loadClass} during fallback autoloading in PHP versions
     * prior to 5.3.0.
     *
     * @param mixed $errno
     * @param mixed $errstr
     * @return void
     */
    public function handleError($errno, $errstr)
    {
        $this->error = true;
    }

    /**
     * Transform the class name to a filename
     *
     * @param  string $class
     * @param  string $directory
     * @return string
     */
    protected function transformClassNameToFilename($class, $directory)
    {
        // $class may contain a namespace portion, in  which case we need
        // to preserve any underscores in that portion.
        $matches = array();
        preg_match('/(?P<namespace>.+\\\)?(?P<class>[^\\\]+$)/', $class, $matches);

        $class     = (isset($matches['class'])) ? $matches['class'] : '';
        $namespace = (isset($matches['namespace'])) ? $matches['namespace'] : '';

        return $directory
             . str_replace(self::NS_SEPARATOR, '/', $namespace)
             . str_replace(self::PREFIX_SEPARATOR, '/', $class)
             . '.php';
    }

    /**
     * Load a class, based on its type (namespaced or prefixed)
     *
     * @param  string $class
     * @param  string $type
     * @return void
     */
    protected function loadClass($class, $type)
    {
        if (!in_array($type, array(self::LOAD_NS, self::LOAD_PREFIX, self::ACT_AS_FALLBACK))) {
            #require_once dirname(__FILE__) . '/Exception/InvalidArgumentException.php';
            throw new Zend_Loader_Exception_InvalidArgumentException();
        }

        // Fallback autoloading
        if ($type === self::ACT_AS_FALLBACK) {
            // create filename
            $filename = $this->transformClassNameToFilename($class, '');
            if (version_compare(PHP_VERSION, '5.3.2', '>=')) {
                $resolvedName = stream_resolve_include_path($filename);
                if ($resolvedName !== false) {
                    return include $resolvedName;
                }
                return false;
            }
            $this->error = false;
            set_error_handler(array($this, 'handleError'), E_WARNING);
            include $filename;
            restore_error_handler();
            if ($this->error) {
                return false;
            }
            return class_exists($class, false);
        }

        // Namespace and/or prefix autoloading
        foreach ($this->$type as $leader => $path) {
            if (0 === strpos($class, $leader)) {
                // Trim off leader (namespace or prefix)
                $trimmedClass = substr($class, strlen($leader));

                // create filename
                $filename = $this->transformClassNameToFilename($trimmedClass, $path);
                if (file_exists($filename)) {
                    return include $filename;
                }
                return false;
            }
        }
        return false;
    }

    /**
     * Normalize the directory to include a trailing directory separator
     *
     * @param  string $directory
     * @return string
     */
    protected function normalizeDirectory($directory)
    {
        $last = $directory[strlen($directory) - 1];
        if (in_array($last, array('/', '\\'))) {
            $directory[strlen($directory) - 1] = DIRECTORY_SEPARATOR;
            return $directory;
        }
        $directory .= DIRECTORY_SEPARATOR;
        return $directory;
    }

}
