<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Utility;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentFile;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Design\Theme\ThemePackage;
use Magento\Framework\View\Design\Theme\ThemePackageList;

/**
 * A helper to gather specific kind of files in Magento application.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Files
{
    const INCLUDE_APP_CODE = 1;

    const INCLUDE_TESTS = 2;

    const INCLUDE_DEV_TOOLS = 4;

    const INCLUDE_TEMPLATES = 8;

    const INCLUDE_LIBS = 16;

    const INCLUDE_PUB_CODE = 32;

    const INCLUDE_NON_CLASSES = 64;

    const INCLUDE_SETUP = 128;

    /**
     * Return as data set
     */
    const AS_DATA_SET = 1024;

    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\App\Utility\Files
     */
    protected static $_instance = null;

    /**
     * @var array
     */
    protected static $_cache = [];

    /**
     * @var DirSearch
     */
    private $dirSearch;

    /**
     * @var ThemePackageList
     */
    private $themePackageList;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var RegexIteratorFactory
     */
    private $regexIteratorFactory;

    /**
     * Constructor
     *
     * @param ComponentRegistrar $componentRegistrar
     * @param DirSearch $dirSearch
     * @param ThemePackageList $themePackageList
     * @param Json|null $serializer
     * @param RegexIteratorFactory|null $regexIteratorFactory
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        DirSearch $dirSearch,
        ThemePackageList $themePackageList,
        Json $serializer = null,
        RegexIteratorFactory $regexIteratorFactory = null
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->dirSearch = $dirSearch;
        $this->themePackageList = $themePackageList;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
        $this->regexIteratorFactory = $regexIteratorFactory ?: ObjectManager::getInstance()
            ->get(RegexIteratorFactory::class);
    }

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
     * @return \Magento\Framework\App\Utility\Files
     * @throws LocalizedException when there is no instance set
     */
    public static function init()
    {
        if (!self::$_instance) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow.FoundDirectThrow
            throw new LocalizedException(__('Instance is not set yet.'));
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
            $key = str_replace(BP . '/', '', $file);
            $result[$key] = [$file];
        }
        return $result;
    }

    /**
     * Get list of regular expressions for matching test directories in modules
     *
     * @return array
     */
    private function getModuleTestDirsRegex()
    {
        $moduleTestDirs = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $moduleTestDirs[] = str_replace('\\', '/', '#' . $moduleDir . '/Test#');
        }
        return $moduleTestDirs;
    }

    /**
     * Get base path
     *
     * @return string
     */
    public function getPathToSource()
    {
        return BP;
    }

    /**
     * Returns list of files, where expected to have class declarations
     *
     * @param int $flags
     * @return array
     */
    public function getPhpFiles($flags = 0)
    {
        // Sets default value
        if ($flags === 0) {
            $flags = self::INCLUDE_APP_CODE
                | self::INCLUDE_TESTS
                | self::INCLUDE_DEV_TOOLS
                | self::INCLUDE_LIBS
                | self::AS_DATA_SET;
        }
        $key = __METHOD__ . BP . $flags;
        if (!isset(self::$_cache[$key])) {
            $files = array_merge(
                $this->getAppCodeFiles($flags),
                $this->getTestFiles($flags),
                $this->getDevToolsFiles($flags),
                $this->getTemplateFiles($flags),
                $this->getLibraryFiles($flags),
                $this->getPubFiles($flags),
                $this->getSetupPhpFiles($flags)
            );
            self::$_cache[$key] = $files;
        }
        if ($flags & self::AS_DATA_SET) {
            return self::composeDataSets(self::$_cache[$key]);
        }
        return self::$_cache[$key];
    }

    /**
     * Return array with all template files
     *
     * @param int $flags
     * @return array
     */
    private function getTemplateFiles($flags)
    {
        if ($flags & self::INCLUDE_TEMPLATES) {
            return $this->getPhtmlFiles(false, false);
        }
        return [];
    }

    /**
     * Return array with all php files related to library
     *
     * @param int $flags
     * @return array
     */
    private function getLibraryFiles($flags)
    {
        if ($flags & self::INCLUDE_LIBS) {
            $libraryExcludeDirs = [];
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libraryDir) {
                $libraryExcludeDirs[] = str_replace('\\', '/', '#' . $libraryDir . '/Test#');
                $libraryExcludeDirs[] = str_replace('\\', '/', '#' . $libraryDir) . '/[\\w]+/Test#';
                if (!($flags & self::INCLUDE_NON_CLASSES)) {
                    $libraryExcludeDirs[] = str_replace('\\', '/', '#' . $libraryDir . '/registration#');
                }
            }
            return $this->getFilesSubset(
                $this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY),
                '*.php',
                $libraryExcludeDirs
            );
        }
        return [];
    }

    /**
     * Return array with all php files related to pub
     *
     * @param int $flags
     * @return array
     */
    private function getPubFiles($flags)
    {
        if ($flags & self::INCLUDE_PUB_CODE) {
            return array_merge(
                Glob::glob(BP . '/*.php', Glob::GLOB_NOSORT),
                Glob::glob(BP . '/pub/*.php', Glob::GLOB_NOSORT)
            );
        }
        return [];
    }

    /**
     * Return array with all php files related to dev tools
     *
     * @param int $flags
     * @return array
     */
    private function getDevToolsFiles($flags)
    {
        if ($flags & self::INCLUDE_DEV_TOOLS) {
            return $this->getFilesSubset([BP . '/dev/tools/Magento'], '*.php', []);
        }
        return [];
    }

    /**
     * Return array with all php files related to modules
     *
     * @param int $flags
     * @return array
     */
    private function getAppCodeFiles($flags)
    {
        if ($flags & self::INCLUDE_APP_CODE) {
            $excludePaths = [];
            $paths = $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE);
            if ($flags & self::INCLUDE_NON_CLASSES) {
                $paths[] = BP . '/app';
            } else {
                foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
                    $excludePaths[] = str_replace('\\', '/', '#' . $moduleDir . '/registration.php#');
                    $excludePaths[] = str_replace('\\', '/', '#' . $moduleDir . '/cli_commands.php#');
                }
            }
            return $this->getFilesSubset(
                $paths,
                '*.php',
                array_merge($this->getModuleTestDirsRegex(), $excludePaths)
            );
        }
        return [];
    }

    /**
     * Return array with all test files
     *
     * @param int $flags
     * @return array
     */
    private function getTestFiles($flags)
    {
        if ($flags & self::INCLUDE_TESTS) {
            $testDirs = [
                BP . '/dev/tests',
                BP . '/setup/src/Magento/Setup/Test',
            ];
            $moduleTestDir = [];
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
                $moduleTestDir[] = $moduleDir . '/Test';
            }
            $libraryTestDirs = [];
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libraryDir) {
                $libraryTestDirs[] = $libraryDir . '/Test';
                $libraryTestDirs[] = $libraryDir . '/*/Test';
            }
            $testDirs = array_merge($testDirs, $moduleTestDir, $libraryTestDirs);
            return self::getFiles($testDirs, '*.php');
        }
        return [];
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
        $cacheKey = __METHOD__ . '|' . implode('|', [$asDataSet]);
        if (!isset(self::$_cache[$cacheKey])) {
            $configXmlPaths = [];
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
                $configXmlPaths[] = $moduleDir . '/etc/config.xml';
                // Module DB-specific configs, e.g. config.mysql4.xml
                $configXmlPaths[] = $moduleDir . '/etc/config.*.xml';
            }
            $globPaths = [BP . '/app/etc/config.xml', BP . '/app/etc/*/config.xml'];
            $configXmlPaths = array_merge($globPaths, $configXmlPaths);
            $files = [];
            foreach ($configXmlPaths as $xmlPath) {
                $files[] = glob($xmlPath, GLOB_NOSORT);
            }
            self::$_cache[$cacheKey] = array_merge([], ...$files);
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
     * @param bool $collectWithContext
     * @return array
     * @codingStandardsIgnoreStart
     */
    public function getConfigFiles(
        $fileNamePattern = '*.xml',
        $excludedFileNames = ['wsdl.xml', 'wsdl2.xml', 'wsi.xml'],
        $asDataSet = true,
        $collectWithContext = false
    ) {
        $cacheKey = __METHOD__ . '|' . $this->serializer->serialize([$fileNamePattern, $excludedFileNames, $asDataSet]);
        if (!isset(self::$_cache[$cacheKey])) {
            $method = $collectWithContext ? 'collectFilesWithContext' : 'collectFiles';
            $files = $this->dirSearch->{$method}(ComponentRegistrar::MODULE, "/etc/{$fileNamePattern}");
            $files = array_filter(
                $files,
                function ($file) use ($excludedFileNames, $collectWithContext) {
                    /** @var ComponentFile $file */
                    return !in_array(basename($collectWithContext ? $file->getFullPath() : $file), $excludedFileNames);
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
     * Returns list of XML related files, used by Magento application
     *
     * @param string $fileNamePattern
     * @param array $excludedFileNames
     * @param bool $asDataSet
     * @return array
     */
    public function getXmlCatalogFiles(
        $fileNamePattern = '*.xsd',
        $excludedFileNames = [],
        $asDataSet = true
    ) {
        $cacheKey = __METHOD__ . '|' . $this->serializer->serialize([$fileNamePattern, $excludedFileNames, $asDataSet]);
        if (!isset(self::$_cache[$cacheKey])) {
            $files = $this->getFilesSubset(
                $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE),
                $fileNamePattern,
                []
            );
            $libraryExcludeDirs = [];
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libraryDir) {
                $libraryExcludeDirs[] = str_replace('\\', '/', '#' . $libraryDir . '/Test#');
                $libraryExcludeDirs[] = str_replace('\\', '/', '#' . $libraryDir) . '/[\\w]+/Test#';
            }
            $files = array_merge(
                $files,
                $this->getFilesSubset(
                    $this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY),
                    $fileNamePattern,
                    $libraryExcludeDirs
                )
            );
            $files = array_merge(
                $files,
                $this->getFilesSubset(
                    $this->componentRegistrar->getPaths(ComponentRegistrar::THEME),
                    $fileNamePattern,
                    []
                )
            );
            $files = array_merge(
                $files,
                $this->getFilesSubset(
                    $this->componentRegistrar->getPaths(ComponentRegistrar::SETUP),
                    $fileNamePattern,
                    []
                )
            );
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
     * Returns a list of configuration files found under theme directories.
     *
     * @param string $fileNamePattern
     * @param bool $asDataSet
     * @return array
     */
    public function getLayoutConfigFiles($fileNamePattern = '*.xml', $asDataSet = true)
    {
        $cacheKey = __METHOD__ . '|' . implode('|', [$fileNamePattern, $asDataSet]);
        if (!isset(self::$_cache[$cacheKey])) {
            self::$_cache[$cacheKey] = $this->dirSearch->collectFiles(
                ComponentRegistrar::THEME,
                "/etc/{$fileNamePattern}"
            );
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
     * Returns list of UI Component files, used by Magento application
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
    public function getUiComponentXmlFiles($incomingParams = [], $asDataSet = true)
    {
        return $this->getLayoutXmlFiles('ui_component', $incomingParams, $asDataSet);
    }

    /**
     * Collect layout files
     *
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
        $cacheKey = hash('sha256', $location . '|' . implode('|', $params));

        if (!isset(self::$_cache[__METHOD__][$cacheKey])) {
            $files = [];
            if ($params['include_code']) {
                $files = array_merge($files, $this->collectModuleLayoutFiles($params, $location));
            }
            if ($params['include_design']) {
                $files = array_merge($files, $this->collectThemeLayoutFiles($params, $location));
            }

            self::$_cache[__METHOD__][$cacheKey] = $files;
        }

        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[__METHOD__][$cacheKey]);
        }
        return self::$_cache[__METHOD__][$cacheKey];
    }

    /**
     * Collect layout files from modules
     *
     * @param array $params
     * @param string $location
     * @return array
     */
    private function collectModuleLayoutFiles(array $params, $location)
    {
        $files = [];
        $area = $params['area'];
        $requiredModuleName = $params['namespace'] . '_' . $params['module'];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            if ($requiredModuleName == '*_*' || $moduleName == $requiredModuleName) {
                $moduleFiles = [];
                $this->_accumulateFilesByPatterns(
                    [$moduleDir . "/view/{$area}/{$location}"],
                    '*.xml',
                    $moduleFiles
                );
                if ($params['with_metainfo']) {
                    foreach ($moduleFiles as $moduleFile) {
                        $modulePath = str_replace(DIRECTORY_SEPARATOR, '/', preg_quote($moduleDir, '#'));
                        $regex = '#^' . $modulePath . '/view/(?P<area>[a-z]+)/layout/(?P<path>.+)$#i';
                        if (preg_match($regex, $moduleFile, $matches)) {
                            $files[] = [
                                [$matches['area'], '', $moduleName, $matches['path'], $moduleFile]
                            ];
                        } else {
                            throw new \UnexpectedValueException("Could not parse modular layout file '$moduleFile'");
                        }
                    }
                } else {
                    $files[] = $moduleFiles;
                }
            }
        }

        return array_merge([], ...$files);
    }

    /**
     * Collect layout files from themes
     *
     * @param array $params
     * @param string $location
     * @return array
     */
    private function collectThemeLayoutFiles(array $params, $location)
    {
        $files = [];
        $area = $params['area'];
        $requiredModuleName = $params['namespace'] . '_' . $params['module'];
        $themePath = $params['theme_path'];
        foreach ($this->themePackageList->getThemes() as $theme) {
            $currentThemePath = str_replace(DIRECTORY_SEPARATOR, '/', $theme->getPath());
            $currentThemeCode = $theme->getVendor() . '/' . $theme->getName();
            if (($area == '*' || $theme->getArea() === $area)
                && ($themePath == '*' || $themePath == '*/*' || $themePath == $currentThemeCode)
            ) {
                $themeFiles = [];
                $this->_accumulateFilesByPatterns(
                    [$currentThemePath . "/{$requiredModuleName}/{$location}"],
                    '*.xml',
                    $themeFiles
                );

                if ($params['with_metainfo']) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $files[] = [array_merge($this->parseThemeFiles($themeFiles, $currentThemePath, $theme))];
                } else {
                    $files[] = $themeFiles;
                }
            }
        }

        return array_merge([], ...$files);
    }

    /**
     * Parse theme layout files
     *
     * @param array $themeFiles
     * @param string $currentThemePath
     * @param ThemePackage $theme
     * @return array
     */
    private function parseThemeFiles($themeFiles, $currentThemePath, $theme)
    {
        $files = [];
        $regex = '#^' . $currentThemePath
            . '/(?P<module>[a-z\d]+_[a-z\d]+)/layout/(override/((base/)|(theme/[a-z\d_]+/[a-z\d_]+/)))?'
            . '(?P<path>.+)$#i';
        foreach ($themeFiles as $themeFile) {
            if (preg_match($regex, $themeFile, $matches)) {
                $files[] = [
                    $theme->getArea(),
                    $theme->getVendor() . '/' . $theme->getName(),
                    $matches['module'],
                    $matches['path'],
                    $themeFile,
                ];
            } else {
                throw new \UnexpectedValueException("Could not parse theme layout file '$themeFile'");
            }
        }
        return $files;
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
        $params = ['namespace' => '*', 'module' => '*', 'area' => '*'];
        foreach (array_keys($params) as $key) {
            if (isset($incomingParams[$key])) {
                $params[$key] = $incomingParams[$key];
            }
        }
        $cacheKey = hash('sha256', implode('|', $params));

        if (!isset(self::$_cache[__METHOD__][$cacheKey])) {
            self::$_cache[__METHOD__][$cacheKey] = self::getFiles(
                $this->getEtcAreaPaths($params['namespace'], $params['module'], $params['area']),
                'page_types.xml'
            );
        }

        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[__METHOD__][$cacheKey]);
        }
        return self::$_cache[__METHOD__][$cacheKey];
    }

    /**
     * Get module etc paths for specified area
     *
     * @param string $namespace
     * @param string $module
     * @param string $area
     * @return array
     */
    private function getEtcAreaPaths($namespace, $module, $area)
    {
        $etcAreaPaths = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            $keyInfo = explode('_', $moduleName);
            if ($keyInfo[0] == $namespace || $namespace == '*') {
                if ($keyInfo[1] == $module || $module == '*') {
                    $etcAreaPaths[] = $moduleDir . "/etc/{$area}";
                }
            }
        }
        return $etcAreaPaths;
    }

    /**
     * Returns list of Javascript files in Magento
     *
     * @param string $area
     * @param string $themePath
     * @param string $namespace
     * @param string $module
     * @return array
     */
    public function getJsFiles($area = '*', $themePath = '*/*', $namespace = '*', $module = '*')
    {
        $key = $area . $themePath . $namespace . $module . __METHOD__ . BP;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $moduleWebPaths = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            $keyInfo = explode('_', $moduleName);
            if ($keyInfo[0] == $namespace || $namespace == '*') {
                if ($keyInfo[1] == $module || $module == '*') {
                    $moduleWebPaths[] = $moduleDir . "/view/{$area}/web";
                }
            }
        }
        $themePaths = $this->getThemePaths($area, $namespace . '_' . $module, '/web');
        $files = self::getFiles(
            array_merge(
                [
                    BP . "/lib/web/{mage,varien}"
                ],
                $themePaths,
                $moduleWebPaths
            ),
            '*.js'
        );
        $result = self::composeDataSets($files);
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * Returns list of all theme paths
     *
     * @param string $area
     * @param string $module
     * @param string $subFolder
     * @return array
     */
    private function getThemePaths($area, $module, $subFolder)
    {
        $themePaths = [];
        foreach ($this->themePackageList->getThemes() as $theme) {
            if ($area == '*' || $theme->getArea() === $area) {
                $themePaths[] = $theme->getPath() . $subFolder;
                $themePaths[] = $theme->getPath() . "/{$module}" . $subFolder;
            }
        }
        return $themePaths;
    }

    /**
     * Returns list of Static HTML files in Magento
     *
     * @param string $area
     * @param string $themePath
     * @param string $namespace
     * @param string $module
     * @return array
     */
    public function getStaticHtmlFiles($area = '*', $themePath = '*/*', $namespace = '*', $module = '*')
    {
        $key = $area . $themePath . $namespace . $module . __METHOD__;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $moduleTemplatePaths = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            $keyInfo = explode('_', $moduleName);
            if ($keyInfo[0] == $namespace || $namespace == '*') {
                if ($keyInfo[1] == $module || $module == '*') {
                    $moduleTemplatePaths[] = $moduleDir . "/view/{$area}/web/template";
                    $moduleTemplatePaths[] = $moduleDir . "/view/{$area}/web/templates";
                }
            }
        }
        $themePaths = $this->getThemePaths($area, $namespace . '_' . $module, '/web/template');
        $files = self::getFiles(
            array_merge(
                $themePaths,
                $moduleTemplatePaths
            ),
            '*.html'
        );
        $result = self::composeDataSets($files);
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * Get list of static view files that are subject of Magento static view files pre-processing system
     *
     * @param string $filePattern
     * @return array
     */
    public function getStaticPreProcessingFiles($filePattern = '*')
    {
        $key = __METHOD__ . '|' . $filePattern;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $area = '*';
        $locale = '*';
        $result = [];
        $moduleLocalePath = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $moduleLocalePath[] = $moduleDir . "/view/{$area}/web/i18n/{$locale}";
        }

        $this->accumulateStaticFiles($area, $filePattern, $result);
        $this->_accumulateFilesByPatterns($moduleLocalePath, $filePattern, $result, '_parseModuleLocaleStatic');
        $this->accumulateThemeStaticFiles($area, $locale, $filePattern, $result);
        self::$_cache[$key] = $result;
        return $result;
    }

    /**
     * Accumulate files from themes
     *
     * @param string $area
     * @param string $locale
     * @param string $filePattern
     * @param array $result
     * @return void
     */
    private function accumulateThemeStaticFiles($area, $locale, $filePattern, &$result)
    {
        foreach ($this->themePackageList->getThemes() as $themePackage) {
            $themeArea = $themePackage->getArea();
            if ($area == '*' || $area == $themeArea) {
                $files = [];
                $themePath = str_replace(DIRECTORY_SEPARATOR, '/', $themePackage->getPath());
                $paths = [
                    $themePath . "/web",
                    $themePath . "/*_*/web",
                    $themePath . "/web/i18n/{$locale}",
                    $themePath . "/*_*/web/i18n/{$locale}"
                ];
                $this->_accumulateFilesByPatterns($paths, $filePattern, $files);
                $regex = '#^' . $themePath .
                    '/((?P<module>[a-z\d]+_[a-z\d]+)/)?web/(i18n/(?P<locale>[a-z_]+)/)?(?P<path>.+)$#i';
                foreach ($files as $file) {
                    if (preg_match($regex, $file, $matches)) {
                        $result[] = [
                            $themeArea,
                            $themePackage->getVendor() . '/' . $themePackage->getName(),
                            $matches['locale'],
                            $matches['module'],
                            $matches['path'],
                            $file,
                        ];
                    } else {
                        throw new \UnexpectedValueException("Could not parse theme static file '$file'");
                    }
                }

                if (!$files) {
                    $result[] = [
                        $themeArea,
                        $themePackage->getVendor() . '/' . $themePackage->getName(),
                        null,
                        null,
                        null,
                        null
                    ];
                }
            }
        }
    }

    /**
     * Get all files from static library directory
     *
     * @return array
     */
    public function getStaticLibraryFiles()
    {
        $result = [];
        $this->_accumulateFilesByPatterns([BP . "/lib/web"], '*', $result, '_parseLibStatic');
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
        $path = str_replace(DIRECTORY_SEPARATOR, '/', BP);
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
     * @deprecated 102.0.4 Replaced with method accumulateStaticFiles()
     *
     * @param string $file
     * @return array
     */
    protected function _parseModuleStatic($file)
    {
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $modulePath) {
            if (preg_match(
                '/^' . preg_quote("{$modulePath}/", '/') . 'view\/([a-z]+)\/web\/(.+)$/i',
                $file,
                $matches
            ) === 1
            ) {
                list(, $area, $filePath) = $matches;
                return [$area, '', '', $moduleName, $filePath, $file];
            }
        }
        return [];
    }

    /**
     * Search static files from all modules by the specified pattern and accumulate meta-info
     *
     * @param string $area
     * @param string $filePattern
     * @param array $result
     * @return void
     */
    private function accumulateStaticFiles($area, $filePattern, array &$result)
    {
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            $moduleWebPath = $moduleDir . "/view/{$area}/web";

            foreach (self::getFiles([$moduleWebPath], $filePattern) as $absolutePath) {
                $localPath = substr($absolutePath, strlen($moduleDir) + 1);
                if (preg_match('/^view\/([a-z]+)\/web\/(.+)$/i', $localPath, $matches) === 1) {
                    list(, $parsedArea, $parsedPath) = $matches;
                    $result[] = [$parsedArea, '', '', $moduleName, $parsedPath, $absolutePath];
                }
            }
        }
    }

    /**
     * Parse meta-info of a localized (translated) static file in module
     *
     * @param string $file
     * @return array
     */
    protected function _parseModuleLocaleStatic($file)
    {
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $modulePath) {
            $appCode = preg_quote("{$modulePath}/", '/');
            if (preg_match('/^' . $appCode . 'view\/([a-z]+)\/web\/i18n\/([a-z_]+)\/(.+)$/i', $file, $matches) === 1) {
                list(, $area, $locale, $filePath) = $matches;
                return [$area, '', $locale, $moduleName, $filePath, $file];
            }
        }
        return [];
    }

    /**
     * Returns list of Javascript files in Magento by certain area
     *
     * @param string $area
     * @return array
     */
    public function getJsFilesForArea($area)
    {
        $key = __METHOD__ . BP . $area;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $viewAreaPaths = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $viewAreaPaths[] = $moduleDir . "/view/{$area}";
        }
        $themePaths = [];
        foreach ($this->themePackageList->getThemes() as $theme) {
            if ($area == '*' || $theme->getArea() === $area) {
                $themePaths[] = $theme->getPath();
            }
        }
        $paths = [
            BP . "/lib/web/varien"
        ];
        $paths = array_merge($paths, $viewAreaPaths, $themePaths);
        $files = self::getFiles($paths, '*.js');

        if ($area == 'adminhtml') {
            $adminhtmlPaths = [BP . "/lib/web/mage/{adminhtml,backend}"];
            $files = array_merge($files, self::getFiles($adminhtmlPaths, '*.js'));
        } else {
            $frontendPaths = [BP . "/lib/web/mage"];
            /* current structure of /lib/web/mage directory contains frontend javascript in the root,
               backend javascript in subdirectories. That's why script shouldn't go recursive through subdirectories
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
        $key = __METHOD__ . (int)$withMetaInfo;
        if (!isset(self::$_cache[$key])) {
            $result = [];
            $this->accumulateModuleTemplateFiles($withMetaInfo, $result);
            $this->accumulateThemeTemplateFiles($withMetaInfo, $result);
            self::$_cache[$key] = $result;
        }
        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$key]);
        }
        return self::$_cache[$key];
    }

    /**
     * Returns list of db_schema files, used by Magento application.
     *
     * @param string $fileNamePattern
     * @param array $excludedFileNames
     * @param bool $asDataSet
     * @return array
     * @codingStandardsIgnoreStart
     */
    public function getDbSchemaFiles(
        $fileNamePattern = 'db_schema.xml',
        $excludedFileNames = [],
        $asDataSet = true
    ) {
        $cacheKey = __METHOD__ . '|' . $this->serializer->serialize([$fileNamePattern, $excludedFileNames, $asDataSet]);
        if (!isset(self::$_cache[$cacheKey])) {
            $files = $this->dirSearch->collectFiles(ComponentRegistrar::MODULE, "/etc/{$fileNamePattern}");
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
     * Collect templates from themes
     *
     * @param bool $withMetaInfo
     * @param array $result
     * @return void
     */
    private function accumulateThemeTemplateFiles($withMetaInfo, array &$result)
    {
        foreach ($this->themePackageList->getThemes() as $theme) {
            $files = [];
            $this->_accumulateFilesByPatterns(
                [$theme->getPath() . '/*_*/templates'],
                '*.phtml',
                $files
            );
            if ($withMetaInfo) {
                $regex = '#^' . str_replace(DIRECTORY_SEPARATOR, '/', $theme->getPath())
                    . '/(?P<module>[a-z\d]+_[a-z\d]+)/templates/(?P<path>.+)$#i';
                foreach ($files as $file) {
                    if (preg_match($regex, $file, $matches)) {
                        $result[] = [
                            $theme->getArea(),
                            $theme->getVendor() . '/' . $theme->getName(),
                            $matches['module'],
                            $matches['path'],
                            $file,
                        ];
                    } else {
                        echo $regex . " - " . $file . "\n";
                        throw new \UnexpectedValueException("Could not parse theme template file '$file'");
                    }
                }
            } else {
                $result = array_merge($result, $files);
            }
        }
    }

    /**
     * Collect templates from modules
     *
     * @param bool $withMetaInfo
     * @param array $result
     * @return void
     */
    private function accumulateModuleTemplateFiles($withMetaInfo, array &$result)
    {
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            $files = [];
            $this->_accumulateFilesByPatterns(
                [$moduleDir . "/view/*/templates"],
                '*.phtml',
                $files
            );
            if ($withMetaInfo) {
                $modulePath = str_replace(DIRECTORY_SEPARATOR, '/', preg_quote($moduleDir, '#'));
                $regex = '#^' . $modulePath . '/view/(?P<area>[a-z]+)/templates/(?P<path>.+)$#i';
                foreach ($files as $file) {
                    if (preg_match($regex, $file, $matches)) {
                        $result[] = [
                            $matches['area'],
                            '',
                            $moduleName,
                            $matches['path'],
                            $file,
                        ];
                    } else {
                        throw new \UnexpectedValueException("Could not parse module template file '$file'");
                    }
                }
            } else {
                $result = array_merge($result, $files);
            }
        }
    }

    /**
     * Returns list of email template files
     *
     * @return array
     */
    public function getEmailTemplates()
    {
        $key = __METHOD__;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }
        $moduleEmailPaths = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $moduleEmailPaths[] = $moduleDir . "/view/email";
        }
        $files = self::getFiles($moduleEmailPaths, '*.html');
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
        $key = __METHOD__ . BP;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }

        $paths = array_merge(
            [BP . '/app', BP . '/dev', BP . '/lib', BP . '/pub'],
            $this->componentRegistrar->getPaths(ComponentRegistrar::LANGUAGE),
            $this->componentRegistrar->getPaths(ComponentRegistrar::THEME),
            $this->getPaths()
        );
        $subFiles = self::getFiles($paths, '*');

        $rootFiles = glob(BP . '/*', GLOB_NOSORT);
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
            $oneDirPattern = str_replace('\\', '/', $oneDirPattern);
            $entriesInDir = Glob::glob("{$oneDirPattern}/{$fileNamePattern}", Glob::GLOB_NOSORT | Glob::GLOB_BRACE);
            $subDirs = Glob::glob("{$oneDirPattern}/*", Glob::GLOB_ONLYDIR | Glob::GLOB_NOSORT | Glob::GLOB_BRACE);
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
        $primaryConfigs = Glob::glob(BP . '/app/etc/{di.xml,*/di.xml}', Glob::GLOB_BRACE);
        $moduleConfigs = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $moduleConfigs = array_merge(
                $moduleConfigs,
                Glob::glob($moduleDir . '/etc/{di,*/di}.xml', Glob::GLOB_BRACE)
            );
        }
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
     * Get module and library paths
     *
     * @return array
     */
    private function getPaths()
    {
        $directories = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $fullModuleDir) {
            $directories[] = $fullModuleDir;
        }
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libraryDir) {
            $directories[] = $libraryDir;
        }
        return $directories;
    }

    /**
     * Check if specified class exists
     *
     * @param string $class
     * @param string &$path
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
            '/dev/tools',
            '/dev/tests/api-functional/framework',
            '/dev/tests/setup-integration/framework',
            '/dev/tests/integration/framework',
            '/dev/tests/integration/framework/tests/unit/testsuite',
            '/dev/tests/integration/testsuite',
            '/dev/tests/integration/testsuite/Magento/Test/Integrity',
            '/dev/tests/static/framework',
            '/dev/tests/static/testsuite',
            '/setup/src'
        ];
        foreach ($directories as $key => $dir) {
            $directories[$key] = BP . $dir;
        }

        $directories = array_merge($directories, $this->getPaths());

        foreach ($directories as $dir) {
            $fullPath = $dir . '/' . $path;
            if ($this->classFileExistsCheckContent($fullPath, $namespace, $className)) {
                return true;
            }
            $classParts = explode('/', $path, 3);
            if (count($classParts) >= 3) {
                // Check if it's PSR-4 class with trimmed vendor and package name parts
                $trimmedFullPath = $dir . '/' . $classParts[2];
                if ($this->classFileExistsCheckContent($trimmedFullPath, $namespace, $className)) {
                    return true;
                }
            }
            $classParts = explode('/', $path, 4);
            if (count($classParts) >= 4) {
                // Check if it's a library under framework directory
                $trimmedFullPath = $dir . '/' . $classParts[3];
                if ($this->classFileExistsCheckContent($trimmedFullPath, $namespace, $className)) {
                    return true;
                }
                $trimmedFullPath = $dir . '/' . $classParts[2] . '/' . $classParts[3];
                if ($this->classFileExistsCheckContent($trimmedFullPath, $namespace, $className)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Helper function for classFileExists to check file content
     *
     * @param string $fullPath
     * @param string $namespace
     * @param string $className
     * @return bool
     */
    private function classFileExistsCheckContent($fullPath, $namespace, $className)
    {
        /**
         * Use realpath() instead of file_exists() to avoid incorrect work on Windows
         * because of case insensitivity of file names
         * Note that realpath() automatically changes directory separator to the OS-native
         * Since realpath won't work with symlinks we also check file_exists if realpath failed
         */
        if (realpath($fullPath) == str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath)
            || file_exists($fullPath)
        ) {
            $fileContent = file_get_contents($fullPath);
            if (strpos($fileContent, 'namespace ' . $namespace) !== false
                && (strpos($fileContent, 'class ' . $className) !== false
                    || strpos($fileContent, 'interface ' . $className) !== false
                    || strpos($fileContent, 'trait ' . $className) !== false)
            ) {
                return true;
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
        $key = __METHOD__;
        if (isset(self::$_cache[$key])) {
            return self::$_cache[$key];
        }

        $result = [];
        foreach (array_keys($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE)) as $moduleName) {
            $namespace = explode('_', $moduleName)[0];
            if (!in_array($namespace, $result) && $namespace !== 'Zend') {
                $result[] = $namespace;
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
        return $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $namespace . '_' . $module) .
            '/' . $file;
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
            $files = self::getFiles(
                [$this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Magento_' . $module)],
                '*.php'
            );
            self::$_cache[$key] = $files;
        }

        if ($asDataSet) {
            return self::composeDataSets(self::$_cache[$key]);
        }

        return self::$_cache[$key];
    }

    /**
     * Returns array of composer.json for components of specified type
     *
     * @param string $componentType
     * @param bool $asDataSet
     * @return array
     */
    public function getComposerFiles($componentType, $asDataSet = true)
    {
        $key = __METHOD__ . '|' . implode('|', [$componentType, $asDataSet]);
        if (!isset(self::$_cache[$key])) {
            $excludes = $componentType == ComponentRegistrar::MODULE ? $this->getModuleTestDirsRegex() : [];
            $files = $this->getFilesSubset(
                $this->componentRegistrar->getPaths($componentType),
                'composer.json',
                $excludes
            );

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
    public function readLists($globPattern)
    {
        $patterns = [];
        foreach (glob($globPattern) as $list) {
            $patterns = array_merge($patterns, file($list, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        }

        // Expand glob patterns
        $result = [];
        $incorrectPatterns = [];
        foreach ($patterns as $pattern) {
            if (0 === strpos($pattern, '#')) {
                continue;
            }
            $patternParts = explode(' ', $pattern);
            if (count($patternParts) == 3) {
                list($componentType, $componentName, $pathPattern) = $patternParts;
                $files = $this->getPathByComponentPattern($componentType, $componentName, $pathPattern);
            } elseif (count($patternParts) == 1) {
                /**
                 * Note that glob() for directories will be returned as is,
                 * but passing directory is supported by the tools (phpcpd, phpmd, phpcs)
                 */
                $files = Glob::glob(BP . '/' . $pattern, Glob::GLOB_BRACE);
            } else {
                throw new \UnexpectedValueException(
                    "Incorrect pattern record '$pattern'. Supported formats: "
                    . "'<componentType> <componentName> <glob_pattern>' or '<glob_pattern>'"
                );
            }
            if (empty($files)) {
                $incorrectPatterns[] = $pattern;
            }
            $result = array_merge($result, $files);
        }
        if (!empty($incorrectPatterns)) {
            throw new LocalizedException(
                __(
                    "The following patterns didn't return any result:\n%1",
                    join("\n", $incorrectPatterns)
                )
            );
        }
        return $result;
    }

    /**
     * Get paths by pattern for specified component component
     *
     * @param string $componentType
     * @param string $componentName
     * @param string $pathPattern
     * @return array
     */
    private function getPathByComponentPattern($componentType, $componentName, $pathPattern)
    {
        $files = [];
        if ($componentType == '*') {
            $componentTypes = [
                ComponentRegistrar::MODULE,
                ComponentRegistrar::LIBRARY,
                ComponentRegistrar::THEME,
                ComponentRegistrar::LANGUAGE,
            ];
        } else {
            $componentTypes = [$componentType];
        }
        foreach ($componentTypes as $type) {
            if ($componentName == '*') {
                $files = array_merge($files, $this->dirSearch->collectFiles($type, $pathPattern));
            } else {
                $componentDir = $this->componentRegistrar->getPath($type, $componentName);
                if (!empty($componentDir)) {
                    $files = array_merge($files, Glob::glob($componentDir . '/' . $pathPattern, Glob::GLOB_BRACE));
                }
            }
        }
        return $files;
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
            self::$_cache[$key] = file_exists(
                $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName)
            );
        }

        return self::$_cache[$key];
    }

    /**
     * Returns list of files in a given directory, minus files in specifically excluded directories.
     *
     * @param array $dirPatterns Directories to search in
     * @param string $fileNamePattern Pattern for filename
     * @param string|array $excludes Subdirectories to exlude, represented as regex
     * @return array Files in $dirPatterns but not in $excludes
     */
    protected function getFilesSubset(array $dirPatterns, $fileNamePattern, $excludes)
    {
        if (!is_array($excludes)) {
            $excludes = [$excludes];
        }
        $fileSet = self::getFiles($dirPatterns, $fileNamePattern);
        foreach ($excludes as $excludeRegex) {
            $fileSet = preg_grep($excludeRegex, $fileSet, PREG_GREP_INVERT);
        }
        return $fileSet;
    }

    /**
     * Get list of PHP files in setup application
     *
     * @param int $flags
     * @return array
     */
    private function getSetupPhpFiles($flags = null)
    {
        $files = [];
        $setupAppPath = BP . '/setup';
        if ($flags & self::INCLUDE_SETUP && file_exists($setupAppPath)) {
            $regexIterator = $this->regexIteratorFactory->create(
                $setupAppPath,
                '/.*php$/'
            );
            foreach ($regexIterator as $file) {
                $files[] = $file[0];
            }
        }
        return $files;
    }
}
