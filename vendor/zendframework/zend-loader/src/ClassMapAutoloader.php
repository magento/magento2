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

// Grab SplAutoloader interface
require_once __DIR__ . '/SplAutoloader.php';

/**
 * Class-map autoloader
 *
 * Utilizes class-map files to lookup classfile locations.
 */
class ClassMapAutoloader implements SplAutoloader
{
    /**
     * Registry of map files that have already been loaded
     * @var array
     */
    protected $mapsLoaded = array();

    /**
     * Class name/filename map
     * @var array
     */
    protected $map = array();

    /**
     * Constructor
     *
     * Create a new instance, and optionally configure the autoloader.
     *
     * @param  null|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Configure the autoloader
     *
     * Proxies to {@link registerAutoloadMaps()}.
     *
     * @param  array|Traversable $options
     * @return ClassMapAutoloader
     */
    public function setOptions($options)
    {
        $this->registerAutoloadMaps($options);
        return $this;
    }

    /**
     * Register an autoload map
     *
     * An autoload map may be either an associative array, or a file returning
     * an associative array.
     *
     * An autoload map should be an associative array containing
     * classname/file pairs.
     *
     * @param  string|array $map
     * @throws Exception\InvalidArgumentException
     * @return ClassMapAutoloader
     */
    public function registerAutoloadMap($map)
    {
        if (is_string($map)) {
            $location = $map;
            if ($this === ($map = $this->loadMapFromFile($location))) {
                return $this;
            }
        }

        if (!is_array($map)) {
            require_once __DIR__ . '/Exception/InvalidArgumentException.php';
            throw new Exception\InvalidArgumentException(sprintf(
                'Map file provided does not return a map. Map file: "%s"',
                (isset($location) && is_string($location) ? $location : 'unexpected type: ' . gettype($map))
            ));
        }

        $this->map = $map + $this->map;

        if (isset($location)) {
            $this->mapsLoaded[] = $location;
        }

        return $this;
    }

    /**
     * Register many autoload maps at once
     *
     * @param  array $locations
     * @throws Exception\InvalidArgumentException
     * @return ClassMapAutoloader
     */
    public function registerAutoloadMaps($locations)
    {
        if (!is_array($locations) && !($locations instanceof Traversable)) {
            require_once __DIR__ . '/Exception/InvalidArgumentException.php';
            throw new Exception\InvalidArgumentException('Map list must be an array or implement Traversable');
        }
        foreach ($locations as $location) {
            $this->registerAutoloadMap($location);
        }
        return $this;
    }

    /**
     * Retrieve current autoload map
     *
     * @return array
     */
    public function getAutoloadMap()
    {
        return $this->map;
    }

    /**
     * {@inheritDoc}
     */
    public function autoload($class)
    {
        if (isset($this->map[$class])) {
            require_once $this->map[$class];

            return $class;
        }

        return false;
    }

    /**
     * Register the autoloader with spl_autoload registry
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register(array($this, 'autoload'), true, true);
    }

    /**
     * Load a map from a file
     *
     * If the map has been previously loaded, returns the current instance;
     * otherwise, returns whatever was returned by calling include() on the
     * location.
     *
     * @param  string $location
     * @return ClassMapAutoloader|mixed
     * @throws Exception\InvalidArgumentException for nonexistent locations
     */
    protected function loadMapFromFile($location)
    {
        if (!file_exists($location)) {
            require_once __DIR__ . '/Exception/InvalidArgumentException.php';
            throw new Exception\InvalidArgumentException(sprintf(
                'Map file provided does not exist. Map file: "%s"',
                (is_string($location) ? $location : 'unexpected type: ' . gettype($location))
            ));
        }

        if (!$path = static::realPharPath($location)) {
            $path = realpath($location);
        }

        if (in_array($path, $this->mapsLoaded)) {
            // Already loaded this map
            return $this;
        }

        $map = include $path;

        return $map;
    }

    /**
     * Resolve the real_path() to a file within a phar.
     *
     * @see https://bugs.php.net/bug.php?id=52769
     * @param  string $path
     * @return string
     */
    public static function realPharPath($path)
    {
        if (!preg_match('|^phar:(/{2,3})|', $path, $match)) {
            return;
        }

        $prefixLength  = 5 + strlen($match[1]);
        $parts = explode('/', str_replace(array('/', '\\'), '/', substr($path, $prefixLength)));
        $parts = array_values(array_filter($parts, function ($p) {
            return ($p !== '' && $p !== '.');
        }));

        array_walk($parts, function ($value, $key) use (&$parts) {
            if ($value === '..') {
                unset($parts[$key], $parts[$key-1]);
                $parts = array_values($parts);
            }
        });

        if (file_exists($realPath = str_pad('phar:', $prefixLength, '/') . implode('/', $parts))) {
            return $realPath;
        }
    }
}
