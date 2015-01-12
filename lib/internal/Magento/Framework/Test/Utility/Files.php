<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Utility;

/**
 * A helper to gather specific kind of files in Magento application
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Files
{
    /**
     * @var \Magento\Framework\Test\Utility\Files
     */
    protected static $_instance = null;

    /**
     * In-memory cache for the data sets
     *
     * @var array
     */
    protected static $_cache = [];

    /**
     * @var string
     */
    protected $_path = '';

    /**
     * Setter for an instance of self
     *
     * Also can unset the current instance, if no arguments are specified
     *
     * @param Files|null $instance
     * @return void
     */
    public static function setInstance(Files $instance = null)
    {
        self::$_instance = $instance;
    }

    /**
     * Getter for an instance of self
     *
     * @return \Magento\Framework\Test\Utility\Files
     * @throws \Exception when there is no instance set
     */
    public static function init()
    {
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
        $result = [];
        foreach ($files as $file) {
            $result[substr($file, strlen(BP))] = [$file];
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
            $namespace = '*';
            $module = '*';
            $files = [];
            if ($appCode) {
                $files = array_merge(
                    glob($this->_path . '/app/*.php', GLOB_NOSORT),
                    self::getFiles(["{$this->_path}/app/code/{$namespace}/{$module}"], '*.php')
                );
            }
            if ($otherCode) {
                $files = array_merge(
                    $files,
                    glob($this->_path . '/*.php', GLOB_NOSORT),
                    glob($this->_path . '/pub/*.php', GLOB_NOSORT),
                    self::getFiles(["{$this->_path}/lib/internal/Magento"], '*.php'),
                    self::getFiles(["{$this->_path}/dev/tools/Magento/Tools/SampleData"], '*.php')
                );
            }
            if ($templates) {
                $files = array_merge($files, $this->getPhtmlFiles(false, false));
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
     * @param bool $lib
     * @param bool $asDataSet
     * @return array
     */
    public function getClassFiles(
        $appCode = true,
        $devTests = true,
        $devTools = true,
        $lib = true,
        $asDataSet = true
    ) {
        $key = __METHOD__ . "/{$this->_path}/{$appCode}/{$devTests}/{$devTools}/{$lib}";
        if (!isset(self::$_cache[$key])) {
            $files = [];
            if ($appCode) {
                $files = array_merge($files, self::getFiles(["{$this->_path}/app/code/Magento"], '*.php'));
            }
            if ($devTests) {
                $files = array_merge($files, self::getFiles(["{$this->_path}/dev/tests"], '*.php'));
            }
            if ($devTools) {
                $files = array_merge($files, self::getFiles(["{$this->_path}/dev/tools/Magento"], '*.php'));
            }
            if ($lib) {
                $files = array_merge($files, self::getFiles(["{$this->_path}/lib/internal/Magento"], '*.php'));
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
            $this->getPageLayoutFiles(),
            $this->getConfigFiles(),
            $this->getDiConfigs(true),
            $this->getLayoutConfigFiles(),
            $this->getPageTypeFiles()
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
            $globPaths = [
                'app/etc/config.xml',
                'app/etc/*/config.xml',
                'app/code/*/*/etc/config.xml',
                'app/code/*/*/etc/config.*.xml' // Module DB-specific configs, e.g. config.mysql4.xml
            ];
            $files = [];
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
     * @codingStandardsIgnoreStart
     */
    public function getConfigFiles(
        $fileNamePattern = '*.xml',
        $excludedFileNames = ['wsdl.xml', 'wsdl2.xml', 'wsi.xml'],
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
    // @codingStandardsIgnoreEnd

    /**
     * Returns a list of configuration files found under the app/design directory.
     *
     * @param string $fileNamePattern
     * @param bool $asDataSet
     * @return array
     */
    public function getLayoutConfigFiles($fileNamePattern = '*.xml', $asDataSet = true)
    {
        $cacheKey = __METHOD__ . '|' . $this->_path . '|' . serialize(func_get_args());
        if (!isset(self::$_cache[$cacheKey])) {
            self::$_cache[$cacheKey] = $this->_getConfigFilesList($fileNamePattern, 'design');
        }
        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$cacheKey]);
        }
        return self::$_cache[$cacheKey];
    }

    /**
     * Returns list of page configuration and generic layout files, used by Magento application modules
     *
     * An incoming array can contain the following items
     * array (
     *     'namespace'      => 'namespace_name',
     *     'module'         => 'module_name',
     *     'area'           => 'area_name',
     *     'theme'          => 'theme_name',
     *     'include_code'   => true|false,
     *     'include_design' => true|false,
     *     'with_metainfo'  => true|false,
     * )
     *
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    public function getLayoutFiles($incomingParams = [], $asDataSet = true)
    {
        return $this->getLayoutXmlFiles('layout', $incomingParams, $asDataSet);
    }

    /**
     * Returns list of page layout files, used by Magento application modules
     *
     * An incoming array can contain the following items
     * array (
     *     'namespace'      => 'namespace_name',
     *     'module'         => 'module_name',
     *     'area'           => 'area_name',
     *     'theme'          => 'theme_name',
     *     'include_code'   => true|false,
     *     'include_design' => true|false,
     *     'with_metainfo'  => true|false,
     * )
     *
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    public function getPageLayoutFiles($incomingParams = [], $asDataSet = true)
    {
        return $this->getLayoutXmlFiles('page_layout', $incomingParams, $asDataSet);
    }

    /**
     * @param string $location
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    protected function getLayoutXmlFiles($location, $incomingParams = [], $asDataSet = true)
    {
        $params = [
            'namespace' => '*',
            'module' => '*',
            'area' => '*',
            'theme_path' => '*/*',
            'include_code' => true,
            'include_design' => true,
            'with_metainfo' => false
        ];
        foreach (array_keys($params) as $key) {
            if (isset($incomingParams[$key])) {
                $params[$key] = $incomingParams[$key];
            }
        }
        $cacheKey = md5($this->_path . '|' . $location . '|' . implode('|', $params));

        if (!isset(self::$_cache[__METHOD__][$cacheKey])) {
            $files = [];
            $area = $params['area'];
            $namespace = $params['namespace'];
            $module = $params['module'];
            if ($params['include_code']) {
                $this->_accumulateFilesByPatterns(
                    ["{$this->_path}/app/code/{$namespace}/{$module}/view/{$area}/{$location}"],
                    '*.xml',
                    $files,
                    $params['with_metainfo'] ? '_parseModuleLayout' : false
                );
            }
            if ($params['include_design']) {
                $this->_accumulateFilesByPatterns(
                    ["{$this->_path}/app/design/{$area}/{$params['theme_path']}/{$namespace}_{$module}/{$location}"],
                    '*.xml',
                    $files,
                    $params['with_metainfo'] ? '_parseThemeLayout' : false
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
     * Parse meta-info of a layout file in module
     *
     * @param string $file
     * @param string $path
     * @return array
     */
    protected function _parseModuleLayout($file, $path)
    {
        preg_match(
            '/^' . preg_quote("{$path}/app/code/", '/') . '([a-z\d]+)\/([a-z\d]+)\/view\/([a-z]+)\/layout\/(.+)$/i',
            $file,
            $matches
        );
        list(, $namespace, $module, $area, $filePath) = $matches;
        return [$area, '', $namespace . '_' . $module, $filePath, $file];
    }

    /**
     * Parse meta-info of a layout file in theme
     *
     * @param string $file
     * @param string $path
     * @return array
     */
    protected function _parseThemeLayout($file, $path)
    {
        $appDesign = preg_quote("{$path}/app/design/", '/');
        $invariant = '/^' . $appDesign . '([a-z\d]+)\/([a-z\d]+)\/([a-z\d_]+)\/([a-z\d]+_[a-z\d]+)\/layout\/';
        if (preg_match($invariant . 'override\/base\/(.+)$/i', $file, $matches)) {
            list(, $area, $themeNS, $themeCode, $module, $filePath) = $matches;
            return [$area, $themeNS . '/' . $themeCode, $module, $filePath];
        }
        if (preg_match($invariant . 'override\/theme\/[a-z\d_]+\/[a-z\d_]+\/(.+)$/i', $file, $matches)) {
            list(, $area, $themeNS, $themeCode, $module, $filePath) = $matches;
            return [$area, $themeNS . '/' . $themeCode, $module, $filePath];
        }
        preg_match($invariant . '(.+)$/i', $file, $matches);
        list(, $area, $themeNS, $themeCode, $module, $filePath) = $matches;
        return [$area, $themeNS . '/' . $themeCode, $module, $filePath, $file];
    }

    /**
     * Returns list of page_type files, used by Magento application modules
     *
     * An incoming array can contain the following items
     * array (
     *     'namespace'      => 'namespace_name',
     *     'module'         => 'module_name',
     *     'area'           => 'area_name',
     *     'theme'          => 'theme_name',
     * )
     *
     * @param array $incomingParams
     * @param bool $asDataSet
     * @return array
     */
    public function getPageTypeFiles($incomingParams = [], $asDataSet = true)
    {
        $params = ['namespace' => '*', 'module' => '*', 'area' => '*', 'theme_path' => '*/*'];
        foreach (array_keys($params) as $key) {
            if (isset($incomingParams[$key])) {
                $params[$key] = $incomingParams[$key];
            }
        }
        $cacheKey = md5($this->_path . '|' . implode('|', $params));

        if (!isset(self::$_cache[__METHOD__][$cacheKey])) {
            $files = [];
            $files = self::getFiles(
                ["{$this->_path}/app/code/{$params['namespace']}/{$params['module']}" . "/etc/{$params['area']}"],
                'page_types.xml'
            );

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
        $namespace = '*';
        $module = '*';
        $area = '*';
        $themePath = '*/*';
        $files = self::getFiles(
            [
                "{$this->_path}/app/code/{$namespace}/{$module}/view/{$area}/web",
                "{$this->_path}/app/design/{$area}/{$themePath}/web",
                "{$this->_path}/app/design/{$area}/{$themePath}/{$module}/web",
                "{$this->_path}/lib/web/{mage,varien}"
            ],
            '*.js'
        );
        $result = self::composeDataSets($files);
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * Get list of static view files that are subject of Magento static view files preprocessing system
     *
     * @param string $filePattern
     * @return array
     */
    public function getStaticPreProcessingFiles($filePattern = '*')
    {
        $key = __METHOD__ . $this->_path . '|' . $filePattern;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $namespace = '*';
        $module = '*';
        $area = '*';
        $themePath = '*/*';
        $locale = '*';
        $result = [];
        $this->_accumulateFilesByPatterns(
            ["{$this->_path}/app/code/{$namespace}/{$module}/view/{$area}/web"],
            $filePattern,
            $result,
            '_parseModuleStatic'
        );
        $this->_accumulateFilesByPatterns(
            ["{$this->_path}/app/code/{$namespace}/{$module}/view/{$area}/web/i18n/{$locale}"],
            $filePattern,
            $result,
            '_parseModuleLocaleStatic'
        );
        $this->_accumulateFilesByPatterns(
            [
                "{$this->_path}/app/design/{$area}/{$themePath}/web",
                "{$this->_path}/app/design/{$area}/{$themePath}/{$module}/web",
            ],
            $filePattern,
            $result,
            '_parseThemeStatic'
        );
        $this->_accumulateFilesByPatterns(
            [
                "{$this->_path}/app/design/{$area}/{$themePath}/web/i18n/{$locale}",
                "{$this->_path}/app/design/{$area}/{$themePath}/{$module}/web/i18n/{$locale}",
            ],
            $filePattern,
            $result,
            '_parseThemeLocaleStatic'
        );
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * Get all files from static library directory
     *
     * @return array
     */
    public function getStaticLibraryFiles()
    {
        $result = [];
        $this->_accumulateFilesByPatterns(["{$this->_path}/lib/web"], '*', $result, '_parseLibStatic');
        return $result;
    }

    /**
     * Parse file path from the absolute path of static library
     *
     * @param string $file
     * @param string $path
     * @return string
     */
    protected function _parseLibStatic($file, $path)
    {
        preg_match('/^' . preg_quote("{$path}/lib/web/", '/') . '(.+)$/i', $file, $matches);
        return $matches[1];
    }

    /**
     * Search files by the specified patterns and accumulate them, applying a callback to each found row
     *
     * @param array $patterns
     * @param string $filePattern
     * @param array $result
     * @param bool $subroutine
     * @return void
     */
    protected function _accumulateFilesByPatterns(array $patterns, $filePattern, array &$result, $subroutine = false)
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $this->_path);
        foreach (self::getFiles($patterns, $filePattern) as $file) {
            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
            if ($subroutine) {
                $result[] = $this->$subroutine($file, $path);
            } else {
                $result[] = $file;
            }
        }
    }

    /**
     * Parse meta-info of a static file in module
     *
     * @param string $file
     * @param string $path
     * @return array
     */
    protected function _parseModuleStatic($file, $path)
    {
        preg_match(
            '/^' . preg_quote("{$path}/app/code/", '/') . '([a-z\d]+)\/([a-z\d]+)\/view\/([a-z]+)\/web\/(.+)$/i',
            $file,
            $matches
        );
        list(, $namespace, $module, $area, $filePath) = $matches;
        return [$area, '', '', $namespace . '_' . $module, $filePath, $file];
    }

    /**
     * Parse meta-info of a localized (translated) static file in module
     *
     * @param string $file
     * @param string $path
     * @return array
     */
    protected function _parseModuleLocaleStatic($file, $path)
    {
        $appCode = preg_quote("{$path}/app/code/", '/');
        preg_match(
            '/^' . $appCode . '([a-z\d]+)\/([a-z\d]+)\/view\/([a-z]+)\/web\/i18n\/([a-z_]+)\/(.+)$/i',
            $file,
            $matches
        );
        list(, $namespace, $module, $area, $locale, $filePath) = $matches;
        return [$area, '', $locale, $namespace . '_' . $module, $filePath, $file];
    }

    /**
     * Parse meta-info of a static file in theme
     *
     * @param string $file
     * @param string $path
     * @return array
     */
    protected function _parseThemeStatic($file, $path)
    {
        $appDesign = preg_quote("{$path}/app/design/", '/');
        if (preg_match(
            '/^' . $appDesign . '([a-z\d]+)\/([a-z\d]+)\/([a-z\d_]+)\/([a-z\d]+_[a-z\d]+)\/web\/(.+)$/i',
            $file,
            $matches
        )) {
            list(, $area, $themeNS, $themeCode, $module, $filePath) = $matches;
            return [$area, $themeNS . '/' . $themeCode, '', $module, $filePath, $file];
        }

        preg_match(
            '/^' . $appDesign . '([a-z\d]+)\/([a-z\d]+)\/([a-z\d_]+)\/web\/(.+)$/i',
            $file,
            $matches
        );
        list(, $area, $themeNS, $themeCode, $filePath) = $matches;
        return [$area, $themeNS . '/' . $themeCode, '', '', $filePath, $file];
    }

    /**
     * Parse meta-info of a localized (translated) static file in theme
     *
     * @param string $file
     * @param string $path
     * @return array
     */
    protected function _parseThemeLocaleStatic($file, $path)
    {
        $design = preg_quote("{$path}/app/design/", '/');
        if (preg_match(
            '/^' . $design. '([a-z\d]+)\/([a-z\d]+)\/([a-z\d_]+)\/([a-z\d]+_[a-z\d]+)\/web\/i18n\/([a-z_]+)\/(.+)$/i',
            $file,
            $matches
        )) {
            list(, $area, $themeNS, $themeCode, $module, $locale, $filePath) = $matches;
            return [$area, $themeNS . '/' . $themeCode, $locale, $module, $filePath, $file];
        }

        preg_match(
            '/^' . $design . '([a-z\d]+)\/([a-z\d]+)\/([a-z\d_]+)\/web\/i18n\/([a-z_]+)\/(.+)$/i',
            $file,
            $matches
        );
        list(, $area, $themeNS, $themeCode, $locale, $filePath) = $matches;
        return [$area, $themeNS . '/' . $themeCode, $locale, '', $filePath, $file];
    }

    /**
     * Returns list of Javascript files in Magento by certain area
     *
     * @param string $area
     * @return array
     */
    public function getJsFilesForArea($area)
    {
        $key = __METHOD__ . $this->_path . $area;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $namespace = $module =  '*';
        $themePath = '*/*';
        $paths = [
            "{$this->_path}/app/code/{$namespace}/{$module}/view/{$area}",
            "{$this->_path}/app/design/{$area}/{$themePath}",
            "{$this->_path}/lib/web/varien"
        ];
        $files = self::getFiles($paths, '*.js');

        if ($area == 'adminhtml') {
            $adminhtmlPaths = ["{$this->_path}/lib/web/mage/{adminhtml,backend}"];
            $files = array_merge($files, self::getFiles($adminhtmlPaths, '*.js'));
        } else {
            $frontendPaths = ["{$this->_path}/lib/web/mage"];
            /* current structure of /lib/web/mage directory contains frontend javascript in the root,
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
     * @param bool $withMetaInfo
     * @param bool $asDataSet
     * @return array
     */
    public function getPhtmlFiles($withMetaInfo = false, $asDataSet = true)
    {
        $key = __METHOD__ . $this->_path . '|' . (int)$withMetaInfo;
        if (!isset(self::$_cache[$key])) {
            $namespace = '*';
            $module = '*';
            $area = '*';
            $themePath = '*/*';
            $result = [];
            $this->_accumulateFilesByPatterns(
                ["{$this->_path}/app/code/{$namespace}/{$module}/view/{$area}/templates"],
                '*.phtml',
                $result,
                $withMetaInfo ? '_parseModuleTemplate' : false
            );
            $this->_accumulateFilesByPatterns(
                ["{$this->_path}/app/design/{$area}/{$themePath}/{$namespace}_{$module}/templates"],
                '*.phtml',
                $result,
                $withMetaInfo ? '_parseThemeTemplate' : false
            );
            self::$_cache[$key] = $result;
        }
        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$key]);
        }
        return self::$_cache[$key];
    }

    /**
     * Parse meta-information from a modular template file
     *
     * @param string $file
     * @param string $path
     * @return array
     */
    protected function _parseModuleTemplate($file, $path)
    {
        preg_match(
            '/^' . preg_quote("{$path}/app/code/", '/') . '([a-z\d]+)\/([a-z\d]+)\/view\/([a-z]+)\/templates\/(.+)$/i',
            $file,
            $matches
        );
        list(, $namespace, $module, $area, $filePath) = $matches;
        return [$area, '', $namespace . '_' . $module, $filePath, $file];
    }

    /**
     * Parse meta-information from a theme template file
     *
     * @param string $file
     * @param string $path
     * @return array
     */
    protected function _parseThemeTemplate($file, $path)
    {
        $appDesign = preg_quote("{$path}/app/design/", '/');
        preg_match(
            '/^' . $appDesign . '([a-z\d]+)\/([a-z\d]+)\/([a-z\d_]+)\/([a-z\d]+_[a-z\d]+)\/templates\/(.+)$/i',
            $file,
            $matches
        );
        list(, $area, $themeNS, $themeCode, $module, $filePath) = $matches;
        return [$area, $themeNS . '/' . $themeCode, $module, $filePath, $file];
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
        $files = self::getFiles([$this->_path . '/app/code/*/*/view/email'], '*.html');
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
            [
                $this->_path . '/app',
                $this->_path . '/dev',
                $this->_path . '/lib',
                $this->_path . '/pub'
            ],
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
     * @param bool $recursive
     * @return array
     */
    public static function getFiles(array $dirPatterns, $fileNamePattern, $recursive = true)
    {
        $result = [];
        foreach ($dirPatterns as $oneDirPattern) {
            $entriesInDir = glob("{$oneDirPattern}/{$fileNamePattern}", GLOB_NOSORT | GLOB_BRACE);
            $subDirs = glob("{$oneDirPattern}/*", GLOB_ONLYDIR | GLOB_NOSORT | GLOB_BRACE);
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
     *
     * @param bool $asDataSet
     * @return array
     */
    public function getDiConfigs($asDataSet = false)
    {
        $primaryConfigs = glob($this->_path . '/app/etc/{di.xml,*/di.xml}', GLOB_BRACE);
        $moduleConfigs = glob($this->_path . '/app/code/*/*/etc/{di,*/di}.xml', GLOB_BRACE);
        $configs = array_merge($primaryConfigs, $moduleConfigs);

        if ($asDataSet) {
            $output = [];
            foreach ($configs as $file) {
                $output[$file] = [$file];
            }

            return $output;
        }
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
        $path = implode('/', explode('\\', $class)) . '.php';
        $directories = [
            '/app/code/',
            '/lib/internal/',
            '/dev/tools/',
            '/dev/tools/performance-toolkit/framework/',
            '/dev/tests/api-functional/framework/',
            '/dev/tests/integration/framework/',
            '/dev/tests/integration/framework/tests/unit/testsuite/',
            '/dev/tests/integration/testsuite/',
            '/dev/tests/integration/testsuite/Magento/Test/Integrity/',
            '/dev/tests/performance/framework/',
            '/dev/tests/static/framework/',
            '/dev/tests/static/testsuite/',
            '/dev/tests/functional/tests/app/',
            '/dev/tests/unit/framework/',
            '/dev/tests/unit/testsuite/'
        ];

        foreach ($directories as $dir) {
            $fullPath = $this->_path . $dir . $path;
            /**
             * Use realpath() instead of file_exists() to avoid incorrect work on Windows because of case insensitivity
             * of file names
             * Note that realpath() automatically changes directory separator to the OS-native
             */
            if (realpath($fullPath) == str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath)) {
                $fileContent = file_get_contents($fullPath);
                if (strpos(
                    $fileContent,
                    'namespace ' . $namespace
                ) !== false && (strpos(
                    $fileContent,
                    'class ' . $className
                ) !== false || strpos(
                    $fileContent,
                    'interface ' . $className
                ) !== false)
                ) {
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
        $result = [];
        foreach ($iterator as $file) {
            if (!$file->isDot() && !in_array($file->getFilename(), ['Zend']) && $file->isDir()) {
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
        return $this->_path . '/app/code/' . $namespace . '/' . $module . '/' . $file;
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
        $pathPattern = $appDir == 'design' ? "/*/*/*/etc/{$fileNamePattern}" : "/*/*/etc/{$fileNamePattern}";
        return glob($this->_path . '/app/' . $appDir . $pathPattern, GLOB_NOSORT | GLOB_BRACE);
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
            $files = self::getFiles(["{$this->_path}/app/code/Magento/{$module}"], '*.php');
            self::$_cache[$key] = $files;
        }

        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$key]);
        }

        return self::$_cache[$key];
    }

    /**
     * Returns array of composer.json for specified app directory, such as code/Magento, design, i18n
     *
     * @param string $appDir
     * @param bool $asDataSet
     * @return array
     */
    public function getComposerFiles($appDir, $asDataSet = true)
    {
        $key = __METHOD__ . '|' . $this->_path . '|' . serialize(func_get_args());
        if (!isset(self::$_cache[$key])) {
            $files = self::getFiles(["{$this->_path}/app/{$appDir}"], 'composer.json');
            self::$_cache[$key] = $files;
        }

        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$key]);
        }

        return self::$_cache[$key];
    }

    /**
     * Read all text files by specified glob pattern and combine them into an array of valid files/directories
     *
     * The Magento root path is prepended to all (non-empty) entries
     *
     * @param string $globPattern
     * @return array
     * @throws \Exception if any of the patterns don't return any result
     */
    public static function readLists($globPattern)
    {
        $patterns = [];
        foreach (glob($globPattern) as $list) {
            $patterns = array_merge($patterns, file($list, FILE_IGNORE_NEW_LINES));
        }

        // Expand glob patterns
        $result = [];
        foreach ($patterns as $pattern) {
            if (0 === strpos($pattern, '#')) {
                continue;
            }
            /**
             * Note that glob() for directories will be returned as is,
             * but passing directory is supported by the tools (phpcpd, phpmd, phpcs)
             */
            $files = glob(self::init()->getPathToSource() . '/' . $pattern, GLOB_BRACE);
            if (empty($files)) {
                throw new \Exception("The glob() pattern '{$pattern}' didn't return any result.");
            }
            $result = array_merge($result, $files);
        }
        return $result;
    }

    /**
     * Check module existence
     *
     * @param string $moduleName
     * @return bool
     */
    public function isModuleExists($moduleName)
    {
        $key = __METHOD__ . "/{$moduleName}";
        if (!isset(self::$_cache[$key])) {
            list($namespace, $module) = explode('_', $moduleName);
            self::$_cache[$key] = file_exists("{$this->_path}/app/code/{$namespace}/{$module}");
        }

        return self::$_cache[$key];
    }
}
