<?php
/**
 * A helper to gather specific kinds if files in Magento application
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     static
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Utility_Files
{
    /**
     * @var Utility_Files
     */
    protected static $_instance = null;

    /**
     * In-memory cache for the data sets
     *
     * @var array
     */
    protected static $_cache = array();

    /**
     * @var string
     */
    protected $_path = '';

    /**
     * Setter/Getter for an instance of self
     *
     * @param Utility_Files $instance
     * @return Utility_Files
     * @throws Exception when there is no instance set
     */
    public static function init(Utility_Files $instance = null)
    {
        if ($instance) {
            self::$_instance = $instance;
        }
        if (!self::$_instance) {
            throw new Exception('Instance is not set yet.');
        }
        return self::$_instance;
    }

    /**
     * Compose PHPUnit's data sets that contain each file as the first argument
     *
     * @param array $files
     * @return array
     */
    public static function composeDataSets(array $files)
    {
        $result = array();
        foreach ($files as $file) {
            /* Use filename as a data set name to not include it to every assertion message */
            $result[$file] = array($file);
        }
        return $result;
    }

    /**
     * Set path to source code
     *
     * @param string $pathToSource
     */
    public function __construct($pathToSource)
    {
        $this->_path = $pathToSource;
    }

    /**
     * Getter for _path
     *
     * @return string
     */
    public function getPathToSource()
    {
        return $this->_path;
    }

    /**
     * Returns array of PHP-files, that use or declare Magento application classes and Magento libs
     *
     * @param bool $appCode   application PHP-code
     * @param bool $otherCode non-application PHP-code (doesn't include "dev" directory)
     * @param bool $templates application PHTML-code
     * @param bool $asDataSet
     * @return array
     */
    public function getPhpFiles($appCode = true, $otherCode = true, $templates = true, $asDataSet = true)
    {
        $key = __METHOD__ . "/{$this->_path}/{$appCode}/{$otherCode}/{$templates}";
        if (!isset(self::$_cache[$key])) {
            $pool = $namespace = $module = $area = $package = $theme = '*';

            $files = array();
            if ($appCode) {
                $files = array_merge(
                    glob($this->_path . '/app/*.php', GLOB_NOSORT),
                    self::_getFiles(array("{$this->_path}/app/code/{$pool}/{$namespace}/{$module}"), '*.php')
                );
            }
            if ($otherCode) {
                $files = array_merge($files,
                    glob($this->_path . '/*.php', GLOB_NOSORT),
                    glob($this->_path . '/pub/*.php', GLOB_NOSORT),
                    self::_getFiles(array("{$this->_path}/downloader"), '*.php'),
                    self::_getFiles(array("{$this->_path}/lib/{Mage,Magento,Varien}"), '*.php')
                );
            }
            if ($templates) {
                $files = array_merge($files,
                    self::_getFiles(array("{$this->_path}/app/code/{$pool}/{$namespace}/{$module}"), '*.phtml'),
                    self::_getFiles(
                        array("{$this->_path}/app/design/{$area}/{$package}/{$theme}/{$namespace}_{$module}"), '*.phtml'
                    )
                );
            }
            self::$_cache[$key] = $files;
        }

        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$key]);
        }
        return self::$_cache[$key];
    }

    /**
     * Returns list of xml files, used by Magento application
     *
     * @return array
     */
    public function getXmlFiles()
    {
        return array_merge(
            self::getConfigFiles(),
            self::getLayoutFiles()
        );
    }

    /**
     * Returns list of configuration files, used by Magento application
     *
     * @param string $fileNamePattern
     * @param array $excludedFileNames
     * @param bool $asDataSet
     * @return array
     */
    public function getConfigFiles(
        $fileNamePattern = '*.xml', $excludedFileNames = array('wsdl.xml', 'wsdl2.xml', 'wsi.xml'), $asDataSet = true
    ) {
        $cacheKey = __METHOD__ . '|' . $this->_path . '|' . serialize(func_get_args());
        if (!isset(self::$_cache[$cacheKey])) {
            $files = glob($this->_path . "/app/code/*/*/*/etc/$fileNamePattern", GLOB_NOSORT | GLOB_BRACE);
            $files = array_filter($files, function ($file) use ($excludedFileNames) {
                return !in_array(basename($file), $excludedFileNames);
            });
            self::$_cache[$cacheKey] = $files;
        }
        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$cacheKey]);
        }
        return self::$_cache[$cacheKey];
    }

    /**
     * Returns list of layout files, used by Magento application modules
     *
     * An incoming array can contain the following items
     * array (
     *     'pool'           => 'pool_name',
     *     'namespace'      => 'namespace_name',
     *     'module'         => 'module_name',
     *     'area'           => 'area_name',
     *     'package'        => 'package_name',
     *     'theme'          => 'theme_name',
     *     'include_code'   => true|false,
     *     'include_design' => true|false,
     * )
     *
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    public function getLayoutFiles($incomingParams = array(), $asDataSet = true)
    {
         $params = array(
            'pool' => '*',
            'namespace' => '*',
            'module' => '*',
            'area' => '*',
            'package' => '*',
            'theme' => '*',
            'include_code' => true,
            'include_design' => true
        );
        foreach (array_keys($params) as $key) {
            if (isset($incomingParams[$key])) {
                $params[$key] = $incomingParams[$key];
            }
        }
        $cacheKey = md5($this->_path . '|' . implode('|', $params));

        if (!isset(self::$_cache[__METHOD__][$cacheKey])) {
            $files = array();
            if ($params['include_code']) {
                $files = self::_getFiles(
                    array("{$this->_path}/app/code/{$params['pool']}/{$params['namespace']}/{$params['module']}"
                        . "/view/{$params['area']}"),
                    '*.xml'
                );
            }
            if ($params['include_design']) {
                $files = array_merge(
                    $files,
                    self::_getFiles(
                        array("{$this->_path}/app/design/{$params['area']}/{$params['package']}/{$params['theme']}"
                            . "/{$params['namespace']}_{$params['module']}"),
                        '*.xml'
                    ),
                    glob(
                        "{$this->_path}/app/design/{$params['area']}/{$params['package']}/{$params['theme']}/local.xml",
                        GLOB_NOSORT
                    )
                );
            }
            self::$_cache[__METHOD__][$cacheKey] = $files;
        }

        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[__METHOD__][$cacheKey]);
        }
        return self::$_cache[__METHOD__][$cacheKey];
    }

    /**
     * Returns list of Javascript files in Magento
     *
     * @return array
     */
    public function getJsFiles()
    {
        $key = __METHOD__ . $this->_path;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $pool = $namespace = $module = $area = $package = $theme = $skin = '*';
        $files = self::_getFiles(
            array(
                "{$this->_path}/app/code/{$pool}/{$namespace}/{$module}/view/{$area}",
                "{$this->_path}/app/design/{$area}/{$package}/{$theme}/skin/{$skin}",
                "{$this->_path}/pub/js/{mage,varien}"
            ),
            '*.js'
        );
        $result = self::composeDataSets($files);
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * Returns list of email template files
     *
     * @return array
     */
    public function getEmailTemplates()
    {
        $key = __METHOD__ . $this->_path;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $files = self::_getFiles(array($this->_path . '/app/code/*/*/*/view/email'), '*.html');
        $result = self::composeDataSets($files);
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * Retrieve all files in folders and sub-folders that match pattern (glob syntax)
     *
     * @param array $dirPatterns
     * @param string $fileNamePattern
     * @return array
     */
    protected static function _getFiles(array $dirPatterns, $fileNamePattern)
    {
        $result = array();
        foreach ($dirPatterns as $oneDirPattern) {
            $filesInDir = glob("$oneDirPattern/$fileNamePattern", GLOB_NOSORT | GLOB_BRACE);
            $subDirs = glob("$oneDirPattern/*", GLOB_ONLYDIR | GLOB_NOSORT | GLOB_BRACE);
            $filesInSubDir = self::_getFiles($subDirs, $fileNamePattern);
            $result = array_merge($result, $filesInDir, $filesInSubDir);
        }
        return $result;
    }

    /**
     * Check if specified class exists within code pools
     *
     * @param string $class
     * @param string &$path
     * @return bool
     */
    public function codePoolClassFileExists($class, &$path = '')
    {
        $path = implode(DIRECTORY_SEPARATOR, explode('_', $class)) . '.php';
        $directories = array('/app/code/core/', '/app/code/community/', '/app/code/local/', '/lib/');
        foreach ($directories as $dir) {
            $fullPath = str_replace('/', DIRECTORY_SEPARATOR, $this->_path . $dir . $path);
            /**
             * Use realpath() instead of file_exists() to avoid incorrect work on Windows because of case insensitivity
             * of file names
             */
            if (realpath($fullPath) == $fullPath) {
                $fileContent = file_get_contents($fullPath);
                if (strpos($fileContent, 'class ' . $class) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}
