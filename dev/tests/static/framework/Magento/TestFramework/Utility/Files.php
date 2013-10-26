<?php
/**
 * A helper to gather specific kind of files in Magento application
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\TestFramework\Utility;

class Files
{
    /**
     * @var \Magento\TestFramework\Utility\Files
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
     * @param \Magento\TestFramework\Utility\Files $instance
     * @return \Magento\TestFramework\Utility\Files
     * @throws \Exception when there is no instance set
     */
    public static function init(\Magento\TestFramework\Utility\Files $instance = null)
    {
        if ($instance) {
            self::$_instance = $instance;
        }
        if (!self::$_instance) {
            throw new \Exception('Instance is not set yet.');
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
            $namespace = $module = $area = $theme = '*';

            $files = array();
            if ($appCode) {
                $files = array_merge(
                    glob($this->_path . '/app/*.php', GLOB_NOSORT),
                    self::getFiles(array("{$this->_path}/app/code/{$namespace}/{$module}"), '*.php')
                );
            }
            if ($otherCode) {
                $files = array_merge(
                    $files,
                    glob($this->_path . '/*.php', GLOB_NOSORT),
                    glob($this->_path . '/pub/*.php', GLOB_NOSORT),
                    self::getFiles(array("{$this->_path}/downloader"), '*.php'),
                    self::getFiles(array("{$this->_path}/lib/{Mage,Magento,Varien}"), '*.php')
                );
            }
            if ($templates) {
                $files = array_merge(
                    $files,
                    self::getFiles(array("{$this->_path}/app/code/{$namespace}/{$module}"), '*.phtml'),
                    self::getFiles(
                        array("{$this->_path}/app/design/{$area}/{$theme}/{$namespace}_{$module}"),
                        '*.phtml'
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
     * Returns list of files, where expected to have class declarations
     *
     * @param bool $appCode   application PHP-code
     * @param bool $devTests
     * @param bool $devTools
     * @param bool $downloaderApp
     * @param bool $downloaderLib
     * @param bool $lib
     * @param bool $asDataSet
     * @return array
     */
    public function getClassFiles(
        $appCode = true, $devTests = true, $devTools = true,
        $downloaderApp = true, $downloaderLib = true, $lib = true, $asDataSet = true
    ) {
        $key = __METHOD__ .
            "/{$this->_path}/{$appCode}/{$devTests}/{$devTools}/{$downloaderApp}/{$downloaderLib}/{$lib}";
        if (!isset(self::$_cache[$key])) {
            $files = array();
            if ($appCode) {
                $files = array_merge(
                    $files,
                    self::getFiles(array("{$this->_path}/app/code/Magento"), '*.php')
                );
            }
            if ($devTests) {
                $files = array_merge(
                    $files,
                    self::getFiles(array("{$this->_path}/dev/tests"), '*.php')
                );
            }
            if ($devTools) {
                $files = array_merge(
                    $files,
                    self::getFiles(array("{$this->_path}/dev/tools/Magento"), '*.php')
                );
            }
            if ($downloaderApp) {
                $files = array_merge(
                    $files,
                    self::getFiles(array("{$this->_path}/downloader/app/Magento"), '*.php')
                );
            }
            if ($downloaderLib) {
                $files = array_merge(
                    $files,
                    self::getFiles(array("{$this->_path}/downloader/lib/Magento"), '*.php')
                );
            }
            if ($lib) {
                $files = array_merge(
                    $files,
                    self::getFiles(array("{$this->_path}/lib/Magento"), '*.php')
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
            $this->getMainConfigFiles(),
            $this->getLayoutFiles(),
            $this->getConfigFiles(),
            $this->getLayoutConfigFiles()
        );
    }

    /**
     * Retrieve all config files, that participate (or have a chance to participate) in composing main config
     *
     * @param bool $asDataSet
     * @return array
     */
    public function getMainConfigFiles($asDataSet = true)
    {
        $cacheKey = __METHOD__ . '|' . $this->_path . '|' . serialize(func_get_args());
        if (!isset(self::$_cache[$cacheKey])) {
            $globPaths = array(
                'app/etc/config.xml',
                'app/etc/*/config.xml',
                'app/etc/local.xml',
                'app/code/*/*/etc/config.xml',
                'app/code/*/*/etc/config.*.xml', // Module DB-specific configs, e.g. config.mysql4.xml
            );
            $files = array();
            foreach ($globPaths as $globPath) {
                $files = array_merge($files, glob($this->_path . '/' . $globPath));
            }
            self::$_cache[$cacheKey] = $files;
        }
        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$cacheKey]);
        }
        return self::$_cache[$cacheKey];
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
        $fileNamePattern = '*.xml',
        $excludedFileNames = array('wsdl.xml', 'wsdl2.xml', 'wsi.xml'),
        $asDataSet = true
    ) {
        $cacheKey = __METHOD__ . '|' . $this->_path . '|' . serialize(func_get_args());
        if (!isset(self::$_cache[$cacheKey])) {
            $files = $this->_getConfigFilesList($fileNamePattern, 'code');
            $files = array_filter(
                $files,
                function ($file) use ($excludedFileNames) {
                    return !in_array(basename($file), $excludedFileNames);
                }
            );
            self::$_cache[$cacheKey] = $files;
        }
        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$cacheKey]);
        }
        return self::$_cache[$cacheKey];
    }

    /**
     * Returns a list of configuration files found under the app/design directory.
     *
     * @param string $fileNamePattern
     * @param bool $asDataSet
     * @return array
     */
    public function getLayoutConfigFiles(
        $fileNamePattern = '*.xml',
        $asDataSet = true
    ) {
        $cacheKey = __METHOD__ . '|' . $this->_path . '|' . serialize(func_get_args());
        if (!isset(self::$_cache[$cacheKey])) {
            self::$_cache[$cacheKey] = $this->_getConfigFilesList($fileNamePattern, 'design');;
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
     *     'namespace'      => 'namespace_name',
     *     'module'         => 'module_name',
     *     'area'           => 'area_name',
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
            'namespace' => '*',
            'module' => '*',
            'area' => '*',
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
                $files = self::getFiles(
                    array(
                        "{$this->_path}/app/code/{$params['namespace']}/{$params['module']}"
                        . "/view/{$params['area']}/layout"
                    ),
                    '*.xml'
                );
            }
            if ($params['include_design']) {
                $themeLayoutDir = "{$this->_path}/app/design/{$params['area']}/{$params['theme']}"
                    . "/{$params['namespace']}_{$params['module']}/layout";
                $dirPatterns = array(
                    $themeLayoutDir,
                    $themeLayoutDir . '/override',
                    $themeLayoutDir . '/override/*/*',
                );
                $files = array_merge(
                    $files,
                    self::getFiles(
                        $dirPatterns,
                        '*.xml'
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
        $namespace = $module = $area = $theme = $skin = '*';
        $files = self::getFiles(
            array(
                "{$this->_path}/app/code/{$namespace}/{$module}/view/{$area}",
                "{$this->_path}/app/design/{$area}/{$theme}/skin/{$skin}",
                "{$this->_path}/pub/lib/{mage,varien}"
            ),
            '*.js'
        );
        $result = self::composeDataSets($files);
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * Returns list of Javascript files in Magento by certain area
     *
     * @return array
     */
    public function getJsFilesForArea($area)
    {
        $key = __METHOD__ . $this->_path . $area;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $namespace = $module = $theme = '*';
        $paths = array(
            "{$this->_path}/app/code/{$namespace}/{$module}/view/{$area}",
            "{$this->_path}/app/design/{$area}/{$theme}",
            "{$this->_path}/pub/lib/varien",
        );
        $files = self::getFiles(
            $paths,
            '*.js'
        );

        if ($area == 'adminhtml') {
            $adminhtmlPaths = array(
                "{$this->_path}/pub/lib/mage/{adminhtml,backend}",
            );
            $files = array_merge($files, self::getFiles($adminhtmlPaths, '*.js'));
        } else {
            $frontendPaths = array("{$this->_path}/pub/lib/mage");
            /* current structure of /pub/lib/mage directory contains frontend javascript in the root,
               backend javascript in subdirectories. That's why script shouldn't go recursive throught subdirectories
               to get js files for frontend */
            $files = array_merge($files, self::getFiles($frontendPaths, '*.js', false));
        }

        self::$_cache[$key] = $files;
        return $files;
    }

    /**
     * Returns list of Phtml files in Magento app directory.
     *
     * @return array
     */
    public function getPhtmlFiles()
    {
        return $this->getPhpFiles(false, false, true, true);
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
        $files = self::getFiles(array($this->_path . '/app/code/*/*/view/email'), '*.html');
        $result = self::composeDataSets($files);
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * Return list of all files. The list excludes tool-specific files
     * (e.g. Git, IDE) or temp files (e.g. in "var/").
     *
     * @return array
     */
    public function getAllFiles()
    {
        $key = __METHOD__ . $this->_path;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }

        $subFiles = self::getFiles(
            array(
                $this->_path . '/app',
                $this->_path . '/dev',
                $this->_path . '/downloader',
                $this->_path . '/lib',
                $this->_path . '/pub',
            ),
            '*'
        );

        $rootFiles = glob($this->_path . '/*', GLOB_NOSORT);
        $rootFiles = array_filter(
            $rootFiles,
            function ($file) {
                return is_file($file);
            }
        );

        $result = array_merge($rootFiles, $subFiles);
        $result = self::composeDataSets($result);

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
    public static function getFiles(array $dirPatterns, $fileNamePattern, $recursive = true)
    {
        $result = array();
        foreach ($dirPatterns as $oneDirPattern) {
            $entriesInDir = glob("$oneDirPattern/$fileNamePattern", GLOB_NOSORT | GLOB_BRACE);
            $subDirs = glob("$oneDirPattern/*", GLOB_ONLYDIR | GLOB_NOSORT | GLOB_BRACE);
            $filesInDir = array_diff($entriesInDir, $subDirs);

            if ($recursive) {
                $filesInSubDir = self::getFiles($subDirs, $fileNamePattern);
                $result = array_merge($result, $filesInDir, $filesInSubDir);
            }
        }
        return $result;
    }

    /**
     * Look for DI config through the system
     * @return array
     */
    public function getDiConfigs()
    {
        $primaryConfigs = glob($this->_path . '/app/etc/{di.xml,*/di.xml}', GLOB_BRACE);
        $moduleConfigs = glob($this->_path . '/app/code/*/*/etc/{di,*/di}.xml', GLOB_BRACE);
        $configs = array_merge($primaryConfigs, $moduleConfigs);
        return $configs;
    }

    /**
     * Check if specified class exists
     *
     * @param string $class
     * @param string &$path
     * @return bool
     */
    public function classFileExists($class, &$path = '')
    {
        if ($class[0] == '\\') {
            $class = substr($class, 1);
        }
        $classParts = explode('\\', $class);
        $className = array_pop($classParts);
        $namespace = implode('\\', $classParts);
        $path = implode(DIRECTORY_SEPARATOR, explode('\\', $class)) . '.php';
        $directories = array(
            '/app/code/', '/lib/', '/downloader/app/', '/downloader/lib/', '/dev/tools/',
            '/dev/tests/api-functional/framework/', '/dev/tests/integration/framework/',
            '/dev/tests/integration/framework/tests/unit/testsuite/', '/dev/tests/integration/testsuite/',
            '/dev/tests/integration/testsuite/Magento/Test/Integrity/', '/dev/tests/performance/framework/',
            '/dev/tests/static/framework/', '/dev/tests/static/testsuite/',
            '/dev/tests/unit/framework/', '/dev/tests/unit/testsuite/',
        );

        foreach ($directories as $dir) {
            $fullPath = str_replace('/', DIRECTORY_SEPARATOR, $this->_path . $dir . $path);
            /**
             * Use realpath() instead of file_exists() to avoid incorrect work on Windows because of case insensitivity
             * of file names
             */
            if (realpath($fullPath) == $fullPath) {
                $fileContent = file_get_contents($fullPath);
                if (strpos($fileContent, 'namespace ' . $namespace) !== false &&
                    (strpos($fileContent, 'class ' . $className) !== false ||
                        strpos($fileContent, 'interface ' . $className) !== false)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return list of declared namespaces
     *
     * @return array
     */
    public function getNamespaces()
    {
        $key = __METHOD__ . $this->_path;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }

        $iterator = new \DirectoryIterator($this->_path . '/app/code/');
        $result = array();
        foreach ($iterator as $file) {
            if (!$file->isDot() && !in_array($file->getFilename(), array('Zend')) && $file->isDir()) {
                $result[] = $file->getFilename();
            }
        }

        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * @param string $namespace
     * @param string $module
     * @param string $file
     * @return string
     */
    public function getModuleFile($namespace, $module, $file)
    {
        return $this->_path . DIRECTORY_SEPARATOR
            . 'app'. DIRECTORY_SEPARATOR
            . 'code'. DIRECTORY_SEPARATOR
            . $namespace . DIRECTORY_SEPARATOR
            . $module . DIRECTORY_SEPARATOR
            . $file;
    }

    /**
     * Helper function for finding config files in various app directories such as 'code' or 'design'.
     *
     * @param string $fileNamePattern can be a glob pattern that represents files to be found.
     * @param string $appDir directory under app folder in which to search (Ex: 'code' or 'design')
     * @return array of strings that represent paths to config files
     */
    protected function _getConfigFilesList($fileNamePattern, $appDir)
    {
        return glob($this->_path . '/app/' . $appDir . "/*/*/etc/$fileNamePattern", GLOB_NOSORT | GLOB_BRACE);

    }

    /**
     * Returns array of PHP-files for specified module
     *
     * @param string $module
     * @param bool $asDataSet
     * @return array
     */
    public function getModulePhpFiles($module, $asDataSet = true)
    {
        $key = __METHOD__ . "/{$module}";
        if (!isset(self::$_cache[$key])) {
            $files = self::getFiles(array("{$this->_path}/app/code/Magento/{$module}"), '*.php');
            self::$_cache[$key] = $files;
        }

        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$key]);
        }

        return self::$_cache[$key];
    }
}
