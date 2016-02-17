<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Loader;

// Grab SplAutoloader interface
require_once __DIR__ . '/SplAutoloader.php';

use GlobIterator;
use Phar;
use PharFileInfo;
use SplFileInfo;
use Traversable;

class ModuleAutoloader implements SplAutoloader
{
    /**
     * @var array An array of module paths to scan
     */
    protected $paths = array();

    /**
     * @var array An array of modulename => path
     */
    protected $explicitPaths = array();

    /**
     * @var array An array of namespaceName => namespacePath
     */
    protected $namespacedPaths = array();

    /**
     * @var string Will contain the absolute phar:// path to the executable when packaged as phar file
     */
    protected $pharBasePath = "";

    /**
     * @var array An array of supported phar extensions (filled on constructor)
     */
    protected $pharExtensions = array();

    /**
     * @var array An array of module classes to their containing files
     */
    protected $moduleClassMap = array();

    /**
     * Constructor
     *
     * Allow configuration of the autoloader via the constructor.
     *
     * @param  null|array|Traversable $options
     */
    public function __construct($options = null)
    {
        if (extension_loaded('phar')) {
            $this->pharBasePath = Phar::running(true);
            $this->pharExtensions = array(
                'phar',
                'phar.tar',
                'tar',
            );

            // ext/zlib enabled -> phar can read gzip & zip compressed files
            if (extension_loaded('zlib')) {
                $this->pharExtensions[] = 'phar.gz';
                $this->pharExtensions[] = 'phar.tar.gz';
                $this->pharExtensions[] = 'tar.gz';

                $this->pharExtensions[] = 'phar.zip';
                $this->pharExtensions[] = 'zip';
            }

            // ext/bzip2 enabled -> phar can read bz2 compressed files
            if (extension_loaded('bzip2')) {
                $this->pharExtensions[] = 'phar.bz2';
                $this->pharExtensions[] = 'phar.tar.bz2';
                $this->pharExtensions[] = 'tar.bz2';
            }
        }

        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Configure the autoloader
     *
     * In most cases, $options should be either an associative array or
     * Traversable object.
     *
     * @param  array|Traversable $options
     * @return ModuleAutoloader
     */
    public function setOptions($options)
    {
        $this->registerPaths($options);
        return $this;
    }

    /**
     * Retrieves the class map for all loaded modules.
     *
     * @return array
     */
    public function getModuleClassMap()
    {
        return $this->moduleClassMap;
    }

    /**
     * Sets the class map used to speed up the module autoloading.
     *
     * @param  array $classmap
     * @return ModuleAutoloader
     */
    public function setModuleClassMap(array $classmap)
    {
        $this->moduleClassMap = $classmap;

        return $this;
    }

    /**
     * Autoload a class
     *
     * @param   $class
     * @return  mixed
     *          False [if unable to load $class]
     *          get_class($class) [if $class is successfully loaded]
     */
    public function autoload($class)
    {
        // Limit scope of this autoloader
        if (substr($class, -7) !== '\Module') {
            return false;
        }

        if (isset($this->moduleClassMap[$class])) {
            require_once $this->moduleClassMap[$class];
            return $class;
        }

        $moduleName = substr($class, 0, -7);
        if (isset($this->explicitPaths[$moduleName])) {
            $classLoaded = $this->loadModuleFromDir($this->explicitPaths[$moduleName], $class);
            if ($classLoaded) {
                return $classLoaded;
            }

            $classLoaded = $this->loadModuleFromPhar($this->explicitPaths[$moduleName], $class);
            if ($classLoaded) {
                return $classLoaded;
            }
        }

        if (count($this->namespacedPaths) >= 1) {
            foreach ($this->namespacedPaths as $namespace => $path) {
                if (false === strpos($moduleName, $namespace)) {
                    continue;
                }

                $moduleNameBuffer = str_replace($namespace . "\\", "", $moduleName);
                $path .= DIRECTORY_SEPARATOR . $moduleNameBuffer . DIRECTORY_SEPARATOR;

                $classLoaded = $this->loadModuleFromDir($path, $class);
                if ($classLoaded) {
                    return $classLoaded;
                }

                $classLoaded = $this->loadModuleFromPhar($path, $class);
                if ($classLoaded) {
                    return $classLoaded;
                }
            }
        }

        $moduleClassPath   = str_replace('\\', DIRECTORY_SEPARATOR, $moduleName);

        $pharSuffixPattern = null;
        if ($this->pharExtensions) {
            $pharSuffixPattern = '(' . implode('|', array_map('preg_quote', $this->pharExtensions)) . ')';
        }

        foreach ($this->paths as $path) {
            $path = $path . $moduleClassPath;

            if ($path == '.' || substr($path, 0, 2) == './' || substr($path, 0, 2) == '.\\') {
                if (!$basePath = $this->pharBasePath) {
                    $basePath = realpath('.');
                }

                if (false === $basePath) {
                    $basePath = getcwd();
                }

                $path = rtrim($basePath, '\/\\') . substr($path, 1);
            }

            $classLoaded = $this->loadModuleFromDir($path, $class);
            if ($classLoaded) {
                return $classLoaded;
            }

            // No directory with Module.php, searching for phars
            if ($pharSuffixPattern) {
                foreach (new GlobIterator($path . '.*') as $entry) {
                    if ($entry->isDir()) {
                        continue;
                    }

                    if (!preg_match('#.+\.' . $pharSuffixPattern . '$#', $entry->getPathname())) {
                        continue;
                    }

                    $classLoaded = $this->loadModuleFromPhar($entry->getPathname(), $class);
                    if ($classLoaded) {
                        return $classLoaded;
                    }
                }
            }
        }

        return false;
    }

    /**
     * loadModuleFromDir
     *
     * @param  string $dirPath
     * @param  string $class
     * @return  mixed
     *          False [if unable to load $class]
     *          get_class($class) [if $class is successfully loaded]
     */
    protected function loadModuleFromDir($dirPath, $class)
    {
        $modulePath = $dirPath . '/Module.php';
        if (substr($modulePath, 0, 7) === 'phar://') {
            $file = new PharFileInfo($modulePath);
        } else {
            $file = new SplFileInfo($modulePath);
        }

        if (($file->isReadable() && $file->isFile())) {
            // Found directory with Module.php in it
            $absModulePath = $this->pharBasePath ? (string) $file : $file->getRealPath();
            require_once $absModulePath;
            if (class_exists($class)) {
                $this->moduleClassMap[$class] = $absModulePath;
                return $class;
            }
        }
        return false;
    }

    /**
     * loadModuleFromPhar
     *
     * @param  string $pharPath
     * @param  string $class
     * @return  mixed
     *          False [if unable to load $class]
     *          get_class($class) [if $class is successfully loaded]
     */
    protected function loadModuleFromPhar($pharPath, $class)
    {
        $pharPath = static::normalizePath($pharPath, false);
        $file = new SplFileInfo($pharPath);
        if (!$file->isReadable() || !$file->isFile()) {
            return false;
        }

        $fileRealPath = $file->getRealPath();

        // Phase 0: Check for executable phar with Module class in stub
        if (strpos($fileRealPath, '.phar') !== false) {
            // First see if the stub makes the Module class available
            require_once $fileRealPath;
            if (class_exists($class)) {
                $this->moduleClassMap[$class] = $fileRealPath;
                return $class;
            }
        }

        // Phase 1: Not executable phar, no stub, or stub did not provide Module class; try Module.php directly
        $moduleClassFile = 'phar://' . $fileRealPath . '/Module.php';
        $moduleFile = new SplFileInfo($moduleClassFile);
        if ($moduleFile->isReadable() && $moduleFile->isFile()) {
            require_once $moduleClassFile;
            if (class_exists($class)) {
                $this->moduleClassMap[$class] = $moduleClassFile;
                return $class;
            }
        }

        // Phase 2: Check for nested module directory within archive
        // Checks for /path/to/MyModule.tar/MyModule/Module.php
        // (shell-integrated zip/tar utilities wrap directories like this)
        $pharBaseName = $this->pharFileToModuleName($fileRealPath);
        $moduleClassFile = 'phar://' . $fileRealPath . '/' . $pharBaseName  . '/Module.php';
        $moduleFile = new SplFileInfo($moduleClassFile);
        if ($moduleFile->isReadable() && $moduleFile->isFile()) {
            require_once $moduleClassFile;
            if (class_exists($class)) {
                $this->moduleClassMap[$class] = $moduleClassFile;
                return $class;
            }
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
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Unregister the autoloader with spl_autoload registry
     *
     * @return void
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'autoload'));
    }

    /**
     * registerPaths
     *
     * @param  array|Traversable $paths
     * @throws \InvalidArgumentException
     * @return ModuleAutoloader
     */
    public function registerPaths($paths)
    {
        if (!is_array($paths) && !$paths instanceof Traversable) {
            require_once __DIR__ . '/Exception/InvalidArgumentException.php';
            throw new Exception\InvalidArgumentException(
                'Parameter to \\Zend\\Loader\\ModuleAutoloader\'s '
                . 'registerPaths method must be an array or '
                . 'implement the Traversable interface'
            );
        }

        foreach ($paths as $module => $path) {
            if (is_string($module)) {
                $this->registerPath($path, $module);
            } else {
                $this->registerPath($path);
            }
        }

        return $this;
    }

    /**
     * registerPath
     *
     * @param  string $path
     * @param  bool|string $moduleName
     * @throws \InvalidArgumentException
     * @return ModuleAutoloader
     */
    public function registerPath($path, $moduleName = false)
    {
        if (!is_string($path)) {
            require_once __DIR__ . '/Exception/InvalidArgumentException.php';
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid path provided; must be a string, received %s',
                gettype($path)
            ));
        }
        if ($moduleName) {
            if (in_array(substr($moduleName, -2), array('\\*', '\\%'))) {
                $this->namespacedPaths[substr($moduleName, 0, -2)] = static::normalizePath($path);
            } else {
                $this->explicitPaths[$moduleName] = static::normalizePath($path);
            }
        } else {
            $this->paths[] = static::normalizePath($path);
        }
        return $this;
    }

    /**
     * getPaths
     *
     * This is primarily for unit testing, but could have other uses.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Returns the base module name from the path to a phar
     *
     * @param  string $pharPath
     * @return string
     */
    protected function pharFileToModuleName($pharPath)
    {
        do {
            $pathinfo = pathinfo($pharPath);
            $pharPath = $pathinfo['filename'];
        } while (isset($pathinfo['extension']));
        return $pathinfo['filename'];
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param  string $path
     * @param  bool   $trailingSlash Whether trailing slash should be included
     * @return string
     */
    public static function normalizePath($path, $trailingSlash = true)
    {
        $path = rtrim($path, '/');
        $path = rtrim($path, '\\');
        if ($trailingSlash) {
            $path .= DIRECTORY_SEPARATOR;
        }
        return $path;
    }
}
