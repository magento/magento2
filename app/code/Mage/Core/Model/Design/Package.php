<?php
/**
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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_Model_Design_Package implements Mage_Core_Model_Design_PackageInterface
{
    /**
     * Regular expressions matches cache
     *
     * @var array
     */
    private static $_regexMatchCache      = array();

    /**
     * Custom theme type cache
     *
     * @var array
     */
    private static $_customThemeTypeCache = array();

    /**
     * Package area
     *
     * @var string
     */
    protected $_area;

    /**
     * Package theme
     *
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * Package root directory
     *
     * @var string
     */
    protected $_rootDir;

    /**
     * Directory of the css file
     * Using only to transmit additional parameter in callback functions
     *
     * @var string
     */
    protected $_callbackFileDir;

    /**
     * List of view configuration objects per theme
     *
     * @var array
     */
    protected $_viewConfigs = array();

    /**
     * Model, used to resolve the file paths
     *
     * @var Mage_Core_Model_Design_FileResolution_StrategyPool
     */
    protected $_resolutionPool = null;

    /**
     * Array of theme model used for fallback mechanism
     *
     * @var array
     */
    protected $_themes = array();

    /**
     * Module configuration reader
     *
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_moduleReader;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Model_App_State
     */
    protected $_appState;

    /**
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Design_FileResolution_StrategyPool $resolutionPool
     * @param Mage_Core_Model_App_State $appState
     */
    public function __construct(
        Mage_Core_Model_Config_Modules_Reader $moduleReader,
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Design_FileResolution_StrategyPool $resolutionPool,
        Mage_Core_Model_App_State $appState
    ) {
        $this->_moduleReader = $moduleReader;
        $this->_filesystem = $filesystem;
        $this->_resolutionPool = $resolutionPool;
        $this->_appState = $appState;
    }

    /**
     * Set package area
     *
     * @param string $area
     * @return Mage_Core_Model_Design_Package
     */
    public function setArea($area)
    {
        $this->_area = $area;
        $this->_theme = null;
        return $this;
    }

    /**
     * Retrieve package area
     *
     * @return string
     */
    public function getArea()
    {
        if (is_null($this->_area)) {
            $this->_area = self::DEFAULT_AREA;
        }
        return $this->_area;
    }

    /**
     * Load design theme
     *
     * @param int|string $themeId
     * @param string|null $area
     * @return Mage_Core_Model_Theme
     */
    protected function _getLoadDesignTheme($themeId, $area = self::DEFAULT_AREA)
    {
        $key = sprintf('%s/%s', $area, $themeId);
        if (isset($this->_themes[$key])) {
            return $this->_themes[$key];
        }

        if (is_numeric($themeId)) {
            $themeModel = clone $this->getDesignTheme();
            $themeModel->load($themeId);
        } else {
            /** @var $collection Mage_Core_Model_Resource_Theme_Collection */
            $collection = $this->getDesignTheme()->getCollection();
            $themeModel = $collection->getThemeByFullPath($area . '/' . $themeId);
        }
        $this->_themes[$key] = $themeModel;

        return $themeModel;
    }

    /**
     * Set theme path
     *
     * @param Mage_Core_Model_Theme|int|string $theme
     * @param string $area
     * @return Mage_Core_Model_Design_Package
     */
    public function setDesignTheme($theme, $area = null)
    {
        if ($area) {
            $this->setArea($area);
        }

        if ($theme instanceof Mage_Core_Model_Theme) {
            $this->_theme = $theme;
        } else {
            $this->_theme = $this->_getLoadDesignTheme($theme, $this->getArea());
        }

        return $this;
    }

    /**
     * Get default theme which declared in configuration
     *
     * @param string $area
     * @param array $params
     * @return string|int
     */
    public function getConfigurationDesignTheme($area = null, array $params = array())
    {
        if (!$area) {
            $area = $this->getArea();
        }
        $store = isset($params['store']) ? $params['store'] : null;

        if ($this->_isThemePerStoveView($area)) {
            return Mage::getStoreConfig(self::XML_PATH_THEME_ID, $store)
                ?: (string)Mage::getConfig()->getNode($area . '/' . self::XML_PATH_THEME);
        }
        return (string)Mage::getConfig()->getNode($area . '/' . self::XML_PATH_THEME);
    }

    /**
     * Whether themes in specified area are supposed to be configured per store view
     *
     * @param string $area
     * @return bool
     */
    private function _isThemePerStoveView($area)
    {
        return $area == self::DEFAULT_AREA;
    }

    /**
     * Set default design theme
     *
     * @return Mage_Core_Model_Design_Package
     */
    public function setDefaultDesignTheme()
    {
        $this->setDesignTheme($this->getConfigurationDesignTheme());
        return $this;
    }

    /**
     * Design theme model getter
     *
     * @return Mage_Core_Model_Theme
     */
    public function getDesignTheme()
    {
        if ($this->_theme === null) {
            $this->_theme = Mage::getModel('Mage_Core_Model_Theme');
        }
        return $this->_theme;
    }

    /**
     * Update required parameters with default values if custom not specified
     *
     * @param array $params
     * @return Mage_Core_Model_Design_Package
     */
    protected function _updateParamDefaults(array &$params)
    {
        if (empty($params['area'])) {
            $params['area'] = $this->getArea();
        }

        if (!empty($params['themeId'])) {
            $params['themeModel'] = $this->_getLoadDesignTheme($params['themeId'], $params['area']);
        } elseif (!empty($params['package']) && isset($params['theme'])) {
            $themePath = $params['package'] . '/' . $params['theme'];
            $params['themeModel'] = $this->_getLoadDesignTheme($themePath, $params['area']);
        } elseif (empty($params['themeModel']) && $params['area'] !== $this->getArea()) {
            $params['themeModel'] = $this->_getLoadDesignTheme(
                $this->getConfigurationDesignTheme($params['area']),
                $params['area']
            );
        } elseif (empty($params['themeModel'])) {
            $params['themeModel'] = $this->getDesignTheme();
        }

        if (!array_key_exists('module', $params)) {
            $params['module'] = false;
        }
        if (empty($params['locale'])) {
            $params['locale'] = Mage::app()->getLocale()->getLocaleCode();
        }
        return $this;
    }

    /**
     * Get existing file name with fallback to default
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getFilename($file, array $params = array())
    {
        $file = $this->_extractScope($file, $params);
        $this->_updateParamDefaults($params);
        $skipProxy = isset($params['skipProxy']) && $params['skipProxy'];
        return  $this->_resolutionPool->getFileStrategy($skipProxy)->getFile($params['area'], $params['themeModel'],
            $file, $params['module']);
    }

    /**
     * Get a locale file
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getLocaleFileName($file, array $params = array())
    {
        $this->_updateParamDefaults($params);
        $skipProxy = isset($params['skipProxy']) && $params['skipProxy'];
        return $this->_resolutionPool->getLocaleStrategy($skipProxy)->getLocaleFile($params['area'],
            $params['themeModel'], $params['locale'], $file);
    }

    /**
     * Find a view file using fallback mechanism
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getViewFile($file, array $params = array())
    {
        $file = $this->_extractScope($file, $params);
        $this->_updateParamDefaults($params);
        $skipProxy = isset($params['skipProxy']) && $params['skipProxy'];
        return $this->_resolutionPool->getViewStrategy($skipProxy)->getViewFile($params['area'],
            $params['themeModel'], $params['locale'], $file, $params['module']);
    }

    /**
     * Identify file scope if it defined in file name and override _module parameter in $params array
     *
     * @param string $file
     * @param array &$params
     * @return string
     * @throws Magento_Exception
     */
    protected function _extractScope($file, array &$params)
    {
        if (strpos(str_replace('\\', '/', $file), './') !== false) {
            throw new Magento_Exception("File name '{$file}' is forbidden for security reasons.");
        }
        if (false !== strpos($file, self::SCOPE_SEPARATOR)) {
            $file = explode(self::SCOPE_SEPARATOR, $file);
            if (empty($file[0])) {
                throw new Magento_Exception('Scope separator "::" cannot be used without scope identifier.');
            }
            $params['module'] = $file[0];
            $file = $file[1];
        }
        return $file;
    }

    /**
     * Notify that view file resolved path was changed (i.e. it was published to a public directory)
     *
     * @param array $params
     * @return Mage_Core_Model_Design_Package
     */
    protected function _notifyViewFileLocationChanged($targetPath, $themeFile, $params)
    {
        $skipProxy = isset($params['skipProxy']) && $params['skipProxy'];
        $strategy = $this->_resolutionPool->getViewStrategy($skipProxy);
        if ($strategy instanceof Mage_Core_Model_Design_FileResolution_Strategy_View_NotifiableInterface) {
            /** @var $strategy Mage_Core_Model_Design_FileResolution_Strategy_View_NotifiableInterface  */
            $themeFile = $this->_extractScope($themeFile, $params);
            $this->_updateParamDefaults($params);
            $strategy->setViewFilePathToMap($params['area'], $params['themeModel'], $params['locale'],
                $params['module'], $themeFile, $targetPath);
        }

        return $this;
    }

    /**
     * Return whether developer mode is turned on
     *
     * @return bool
     */
    protected function _getAppMode()
    {
        return $this->_appState->getMode();
    }

    /**
     * Verify whether we should work with files
     *
     * @return bool
     */
    protected function _isViewFileOperationAllowed()
    {
        return $this->_getAppMode() != Mage_Core_Model_App_State::MODE_PRODUCTION;
    }

    /**
     * Return package name based on design exception rules
     *
     * @param array $rules - design exception rules
     * @param string $regexpsConfigPath
     * @return bool|string
     */
    public static function getPackageByUserAgent(array $rules, $regexpsConfigPath = 'path_mock')
    {
        foreach ($rules as $rule) {
            if (!empty(self::$_regexMatchCache[$rule['regexp']][$_SERVER['HTTP_USER_AGENT']])) {
                self::$_customThemeTypeCache[$regexpsConfigPath] = $rule['value'];
                return $rule['value'];
            }

            $regexp = '/' . trim($rule['regexp'], '/') . '/';

            if (@preg_match($regexp, $_SERVER['HTTP_USER_AGENT'])) {
                self::$_regexMatchCache[$rule['regexp']][$_SERVER['HTTP_USER_AGENT']] = true;
                self::$_customThemeTypeCache[$regexpsConfigPath] = $rule['value'];
                return $rule['value'];
            }
        }

        return false;
    }

    /**
     * Remove all merged js/css files
     *
     * @return bool
     * @throws Magento_Exception
     */
    public function cleanMergedJsCss()
    {
        if (!$this->isMergingViewFilesAllowed()) {
            throw new Magento_Exception('Cleaning of merged view files is not allowed');
        }

        $dir = $this->_buildPublicViewFilename(self::PUBLIC_MERGE_DIR);
        try {
            $this->_filesystem->delete($dir);
            $deleted = true;
        } catch (Magento_Filesystem_Exception $e) {
            $deleted = false;
        }
        return $deleted && Mage::helper('Mage_Core_Helper_File_Storage_Database')->deleteFolder($dir);
    }

    /**
     * Get url to file base on theme file identifier.
     * Publishes file there, if needed.
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($file, array $params = array())
    {
        $isSecure = isset($params['_secure']) ? (bool) $params['_secure'] : null;
        unset($params['_secure']);
        $this->_updateParamDefaults($params);
        $file = $this->_extractScope($file, $params);

        // Build public url to it
        if ($this->_isViewFileOperationAllowed()) {
            /* Identify public file */
            $publicFile = $this->_publishViewFile($file, $params);
            /* Build url to public file */
            $url = $this->getPublicFileUrl($publicFile, $isSecure);
            if (Mage::helper('Mage_Core_Helper_Data')->isStaticFilesSigned()) {
                $fileMTime = $this->_filesystem->getMTime($publicFile);
                $url .= '?' . $fileMTime;
            }
        } else {
            /** @var $themeModel Mage_Core_Model_Theme */
            $themeModel = $params['themeModel'];
            $themePath = $themeModel->getThemePath();
            if (!$themePath) {
                // For virtual themes we get path from the parent
                $themePath = $themeModel->getParentTheme()->getThemePath();
            }
            $subPath = self::getPublishedViewFileRelPath($params['area'], $themePath, $params['locale'], $file,
                $params['module']);
            $url = $this->getPublicFileUrl($this->getPublicDir() . DIRECTORY_SEPARATOR . $subPath, $isSecure);
        }

        return $url;
    }

    /**
     * Get url to public file
     *
     * @param string $file
     * @param bool|null $isSecure
     * @return string
     * @throws Magento_Exception
     */
    public function getPublicFileUrl($file, $isSecure = null)
    {
        foreach (array(
                Mage_Core_Model_Store::URL_TYPE_LIB => Mage_Core_Model_Dir::PUB_LIB,
                Mage_Core_Model_Store::URL_TYPE_MEDIA => Mage_Core_Model_Dir::MEDIA,
                Mage_Core_Model_Store::URL_TYPE_STATIC => Mage_Core_Model_Dir::STATIC_VIEW
            ) as $urlType => $dirType
        ) {
            $dir = Mage::getBaseDir($dirType);
            if (strpos($file, $dir) === 0) {
                $relativePath = ltrim(substr($file, strlen($dir)), DIRECTORY_SEPARATOR);
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
                return Mage::getBaseUrl($urlType, $isSecure) . $relativePath;
            }
        }
        throw new Magento_Exception(
            "Cannot build URL for the file '$file' because it does not reside in a public directory."
        );
    }

    /**
     * Check, if requested theme file has public access, and move it to public folder, if the file has no public access
     *
     * @param  string $themeFile
     * @param  array $params
     * @return string
     * @throws Magento_Exception
     */
    protected function _publishViewFile($themeFile, $params)
    {
        if (!$this->_isViewFileOperationAllowed()) {
            throw new Magento_Exception('Filesystem operations are not permitted for view files');
        }

        $sourcePath = $this->getViewFile($themeFile, $params);

        $minifiedSourcePath = $this->_minifiedPathForStaticFiles($sourcePath);
        if ($minifiedSourcePath && ($this->_getAppMode() != Mage_Core_Model_App_State::MODE_DEVELOPER)
            && $this->_filesystem->has($minifiedSourcePath)
        ) {
            $sourcePath = $minifiedSourcePath;
            $themeFile = $this->_minifiedPathForStaticFiles($themeFile);
        }

        if (!$this->_filesystem->has($sourcePath)) {
            throw new Magento_Exception("Unable to locate theme file '{$sourcePath}'.");
        }
        if (!$this->_needToProcessFile($sourcePath)) {
            return $sourcePath;
        }

        $allowPublication = (string)Mage::getConfig()->getNode(self::XML_PATH_ALLOW_DUPLICATION);
        if ($allowPublication || $this->_getExtension($themeFile) == self::CONTENT_TYPE_CSS) {
            $targetPath = $this->_buildPublicViewRedundantFilename($themeFile, $params);
        } else {
            $targetPath = $this->_buildPublicViewSufficientFilename($sourcePath, $params);
        }
        $targetPath = $this->_buildPublicViewFilename($targetPath);

        /* Validate whether file needs to be published */
        if ($this->_getExtension($themeFile) == self::CONTENT_TYPE_CSS) {
            $cssContent = $this->_getPublicCssContent($sourcePath, dirname($targetPath), $themeFile, $params);
        }

        $fileMTime = $this->_filesystem->getMTime($sourcePath);
        if (!$this->_filesystem->has($targetPath) || $fileMTime != $this->_filesystem->getMTime($targetPath)) {
            $publicDir = dirname($targetPath);
            if (!$this->_filesystem->isDirectory($publicDir)) {
                $this->_filesystem->createDirectory($publicDir, 0777);
            }

            if (isset($cssContent)) {
                $this->_filesystem->write($targetPath, $cssContent);
                $this->_filesystem->touch($targetPath, $fileMTime);
            } elseif ($this->_filesystem->isFile($sourcePath)) {
                $this->_filesystem->copy($sourcePath, $targetPath);
                $this->_filesystem->touch($targetPath, $fileMTime);
            } elseif (!$this->_filesystem->isDirectory($targetPath)) {
                $this->_filesystem->createDirectory($targetPath, 0777);
            }
        }

        $this->_notifyViewFileLocationChanged($targetPath, $themeFile, $params);
        return $targetPath;
    }

    /**
     * Get minified filename for static files
     *
     * @param string $filePath
     * @return string|null
     */
    protected function _minifiedPathForStaticFiles($filePath)
    {
        $extension = $this->_getExtension($filePath);
        return in_array($extension, array(self::CONTENT_TYPE_JS, self::CONTENT_TYPE_CSS))
            ? str_replace('.' . $extension, '.min.' . $extension, $filePath)
            : null;
    }

    /**
     * Determine whether a file needs to be published.
     * Js files are never processed. All other files must be processed either if they are not published already,
     * or if they are css-files and we're working in developer mode.
     *
     * @param string $filePath
     * @return bool
     */
    protected function _needToProcessFile($filePath)
    {
        $jsPath = Mage::getBaseDir(Mage_Core_Model_Dir::PUB_LIB) . DS;
        if (strncmp($filePath, $jsPath, strlen($jsPath)) === 0) {
            return false;
        }

        $protectedExtensions = array(self::CONTENT_TYPE_PHP, self::CONTENT_TYPE_PHTML, self::CONTENT_TYPE_XML);
        if (in_array($this->_getExtension($filePath), $protectedExtensions)) {
            return false;
        }

        $themePath = $this->getPublicDir() . DS;
        if (strncmp($filePath, $themePath, strlen($themePath)) !== 0) {
            return true;
        }

        return ($this->_getAppMode() == Mage_Core_Model_App_State::MODE_DEVELOPER)
            && $this->_getExtension($filePath) == self::CONTENT_TYPE_CSS;
    }

    /**
     * Get file extension by file path
     *
     * @param string $filePath
     * @return string
     */
    protected function _getExtension($filePath)
    {
        $dotPosition = strrpos($filePath, '.');
        return strtolower(substr($filePath, $dotPosition + 1));
    }

    /**
     * Build path to file located in public folder
     *
     * @param string $file
     * @return string
     */
    protected function _buildPublicViewFilename($file)
    {
        return $this->getPublicDir() . DS . $file;
    }

    /**
     * Return directory for theme files publication
     *
     * @return string
     */
    public function getPublicDir()
    {
        return Mage::getBaseDir(Mage_Core_Model_Dir::STATIC_VIEW);
    }

    /**
     * Build public filename for a theme file that always includes area/package/theme/locate parameters
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    protected function _buildPublicViewRedundantFilename($file, array $params)
    {
        if ($params['themeModel']->getThemePath()) {
            $designPath = str_replace('/', DS, $params['themeModel']->getThemePath());
        } elseif ($params['themeModel']->getId()) {
            $designPath = self::PUBLIC_THEME_DIR . $params['themeModel']->getId();
        } else {
            $designPath = self::PUBLIC_VIEW_DIR;
        }

        $publicFile = $params['area'] . DS . $designPath . DS . $params['locale'] .
            ($params['module'] ? DS . $params['module'] : '') . DS . $file;

        return $publicFile;
    }

    /**
     * Build public filename for a view file that sufficiently depends on the passed parameters
     *
     * @param string $filename
     * @param array $params
     * @return string
     */
    protected function _buildPublicViewSufficientFilename($filename, array $params)
    {
        $designDir = Mage::getBaseDir(Mage_Core_Model_Dir::THEMES) . DS;
        if (0 === strpos($filename, $designDir)) {
            // theme file
            $publicFile = substr($filename, strlen($designDir));
        } else {
            // modular file
            $module = $params['module'];
            $moduleDir = Mage::getModuleDir('theme', $module) . DS;
            $publicFile = substr($filename, strlen($moduleDir));
            $publicFile = self::PUBLIC_MODULE_DIR . DS . $module . DS . $publicFile;
        }
        return $publicFile;
    }

    /**
     * Extract non-absolute URLs from a CSS content
     *
     * @param string $cssContent
     * @return array
     */
    protected function _extractCssRelativeUrls($cssContent)
    {
        preg_match_all(self::REGEX_CSS_RELATIVE_URLS, $cssContent, $matches);
        if (!empty($matches[0]) && !empty($matches[1])) {
            return array_combine($matches[0], $matches[1]);
        }
        return array();
    }

    /**
     * Retrieve processed CSS file content that contains URLs relative to the specified public directory
     *
     * @param string $filePath Absolute path to the CSS file
     * @param string $publicDir Absolute path to the public directory to which URLs should be relative
     * @param string $fileName File name used for reference
     * @param array $params Design parameters
     * @return string
     */
    protected function _getPublicCssContent($filePath, $publicDir, $fileName, $params)
    {
        $content = $this->_filesystem->read($filePath);
        $relativeUrls = $this->_extractCssRelativeUrls($content);
        foreach ($relativeUrls as $urlNotation => $fileUrl) {
            try {
                $relatedFilePathPublic = $this->_publishRelatedViewFile($fileUrl, $filePath, $fileName, $params);
                $fileUrlNew = basename($relatedFilePathPublic);
                $offset = $this->_getFilesOffset($relatedFilePathPublic, $publicDir);
                if ($offset) {
                    $fileUrlNew = $this->_canonize($offset . '/' . $fileUrlNew, true);
                }
                $urlNotationNew = str_replace($fileUrl, $fileUrlNew, $urlNotation);
                $content = str_replace($urlNotation, $urlNotationNew, $content);
            } catch (Magento_Exception $e) {
                Mage::logException($e);
            }
        }
        return $content;
    }

    /**
     * Publish relative $fileUrl based on information about parent file path and name
     *
     * @param string $fileUrl URL to the file that was extracted from $parentFilePath
     * @param string $parentFilePath path to the file
     * @param string $parentFileName original file name identifier that was requested for processing
     * @param array $params theme/module parameters array
     * @return string
     */
    protected function _publishRelatedViewFile($fileUrl, $parentFilePath, $parentFileName, $params)
    {
        if (strpos($fileUrl, self::SCOPE_SEPARATOR)) {
            $relativeThemeFile = $this->_extractScope($fileUrl, $params);
        } else {
            /* Check if module file overridden on theme level based on _module property and file path */
            if ($params['module'] && strpos($parentFilePath, Mage::getBaseDir(Mage_Core_Model_Dir::THEMES)) === 0) {
                /* Add module directory to relative URL for canonization */
                $relativeThemeFile = dirname($params['module'] . DS . $parentFileName)
                    . DS . $fileUrl;
                $relativeThemeFile   = $this->_canonize($relativeThemeFile);
                if (strpos($relativeThemeFile, $params['module']) === 0) {
                    $relativeThemeFile = str_replace($params['module'], '', $relativeThemeFile);
                } else {
                    $params['module'] = false;
                }
            } else {
                $relativeThemeFile = $this->_canonize(dirname($parentFileName) . DS . $fileUrl);
            }
        }
        return $this->_publishViewFile($relativeThemeFile, $params);
    }

    /**
     * Canonize the specified filename
     *
     * Removes excessive "./" and "../" from the path.
     * Returns false, if cannot get rid of all "../"
     *
     * @param string $filename
     * @param bool $isRelative flag that identify that filename is relative
     * @return string
     * @throws Magento_Exception if file can't be canonized
     */
    protected function _canonize($filename, $isRelative = false)
    {
        $result = array();
        $parts = explode('/', str_replace('\\', '/', $filename));
        $prefix = '';
        if ($isRelative) {
            foreach ($parts as $part) {
                if ($part != '..') {
                    break;
                }
                $prefix .= '../';
                array_shift($parts);
            }
        }

        foreach ($parts as $part) {
            if ('..' === $part) {
                if (null === array_pop($result)) {
                    throw new Magento_Exception("Invalid file '{$filename}'.");
                }
            } elseif ('.' !== $part) {
                $result[] = $part;
            }
        }
        return $prefix . implode('/', $result);
    }

    /**
     * Build a relative path to a static view file, if published with duplication.
     *
     * Just concatenates all context arguments.
     * Note: despite $locale is specified, it is currently ignored.
     *
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public static function getPublishedViewFileRelPath($area, $themePath, $locale, $file, $module = null)
    {
        return $area . DIRECTORY_SEPARATOR . $themePath . DIRECTORY_SEPARATOR
            . ($module ? $module . DIRECTORY_SEPARATOR : '') . $file;
    }

    /**
     * Merge files, located under the same folder, into one and return file name of merged file
     *
     * @param array $files list of names relative to the same folder
     * @param string $contentType
     * @return string
     * @throws Magento_Exception if not existing file requested for merge
     */
    public function mergeFiles($files, $contentType)
    {
        if (!$this->isMergingViewFilesAllowed()) {
            throw new Magento_Exception('Merging of view files is not allowed');
        }

        $filesToMerge = array();
        $mergedFile = array();
        $jsDir = Mage::getBaseDir(Mage_Core_Model_Dir::PUB_LIB);
        $publicDir = $this->_buildPublicViewFilename('');
        foreach ($files as $file) {
            $params = array();
            $this->_updateParamDefaults($params);
            $filesToMerge[$file] = $this->_publishViewFile($file, $params);
            $mergedFile[] = str_replace('\\', '/', str_replace(array($jsDir, $publicDir), '', $filesToMerge[$file]));
        }
        $mergedFile = self::PUBLIC_MERGE_DIR . DS . md5(implode('|', $mergedFile)) . ".{$contentType}";
        $mergedFile = $this->_buildPublicViewFilename($mergedFile);
        $mergedMTimeFile  = $mergedFile . '.dat';
        $filesMTimeData = '';
        foreach ($filesToMerge as $file) {
            $filesMTimeData .= $this->_filesystem->getMTime($file);
        }
        if ($this->_filesystem->has($mergedFile) && $this->_filesystem->has($mergedMTimeFile)
            && ($filesMTimeData == $this->_filesystem->read($mergedMTimeFile))
        ) {
            return $mergedFile;
        }
        if (!$this->_filesystem->isDirectory(dirname($mergedFile))) {
            $this->_filesystem->createDirectory(dirname($mergedFile), 0777);
        }

        $result = array();
        foreach ($filesToMerge as $file) {
            if (!$this->_filesystem->has($file)) {
                throw new Magento_Exception("Unable to locate file '{$file}' for merging.");
            }
            $content = $this->_filesystem->read($file);
            if ($contentType == self::CONTENT_TYPE_CSS) {
                $offset = $this->_getFilesOffset($file, dirname($mergedFile));
                $content = $this->_applyCssUrlOffset($content, $offset);
            }
            $result[] = $content;
        }
        $result = ltrim(implode($result));
        if ($contentType == self::CONTENT_TYPE_CSS) {
            $result = $this->_popCssImportsUp($result);
        }

        $this->_filesystem->write($mergedFile, $result);
        $this->_filesystem->write($mergedMTimeFile, $filesMTimeData);
        return $mergedFile;
    }

    /**
     * Return whether view files merging is allowed or not
     *
     * @return bool
     */
    public function isMergingViewFilesAllowed()
    {
        return $this->_isViewFileOperationAllowed();
    }

    /**
     * Replace relative URLs in the CSS content with ones shifted by the directories offset
     *
     * @throws Magento_Exception
     * @param string $cssContent
     * @param string $relativeOffset
     * @return string
     */
    protected function _applyCssUrlOffset($cssContent, $relativeOffset)
    {
        $relativeUrls = $this->_extractCssRelativeUrls($cssContent);
        foreach ($relativeUrls as $urlNotation => $fileUrl) {
            if (strpos($fileUrl, self::SCOPE_SEPARATOR)) {
                throw new Magento_Exception(
                    'URL offset cannot be applied to CSS content that contains scope separator.'
                );
            }
            $fileUrlNew = $this->_canonize($relativeOffset . '/' . $fileUrl, true);
            $urlNotationNew = str_replace($fileUrl, $fileUrlNew, $urlNotation);
            $cssContent = str_replace($urlNotation, $urlNotationNew, $cssContent);
        }
        return $cssContent;
    }

    /**
     * Calculate offset between public file and public directory
     *
     * Case 1: private file to public folder - Exception;
     *  app/design/frontend/default/default/default/style.css
     *  pub/theme/frontend/default/default/default/style.css
     *
     * Case 2: public file to public folder - $fileOffset = '../frontend/default/default/default';
     *  pub/theme/frontend/default/default/default/style.css -> img/empty.gif
     *  pub/theme/_merged/hash.css -> ../frontend/default/default/default/img/empty.gif
     *
     * @param string $originalFile path to original file
     * @param string $relocationDir path to directory where content will be relocated
     * @return string
     * @throws Magento_Exception
     */
    protected function _getFilesOffset($originalFile, $relocationDir)
    {
        $publicDir = Mage::getBaseDir();
        if (strpos($originalFile, $publicDir) !== 0 || strpos($relocationDir, $publicDir) !== 0) {
            throw new Magento_Exception('Offset can be calculated for public resources only.');
        }
        $offset = '';
        while ($relocationDir != $publicDir && strpos($originalFile, $relocationDir) !== 0) {
            $relocationDir = dirname($relocationDir);
            $offset .= '../';
        }
        $suffix = str_replace($relocationDir, '', dirname($originalFile));
        $offset = rtrim($offset . ltrim($suffix, '\/'), '\/');
        $offset = str_replace(DS, '/', $offset);
        return $offset;
    }

    /**
     * Put CSS import directives to the start of CSS content
     *
     * @param string $contents
     * @return string
     */
    protected function _popCssImportsUp($contents)
    {
        $parts = preg_split('/(@import\s.+?;\s*)/', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);
        $imports = array();
        $css = array();
        foreach ($parts as $part) {
            if (0 === strpos($part, '@import', 0)) {
                $imports[] = trim($part);
            } else {
                $css[] = $part;
            }
        }

        $result = implode($css);
        if ($imports) {
            $result = implode("\n", $imports) . "\n" . "/* Import directives above popped up. */\n" . $result;
        }
        return $result;
    }

    /**
     * Render view config object for current package and theme
     *
     * @param array $params
     * @return Magento_Config_View
     */
    public function getViewConfig(array $params = array())
    {
        $this->_updateParamDefaults($params);
        /** @var $currentTheme Mage_Core_Model_Theme */
        $currentTheme = $params['themeModel'];
        $key = $currentTheme->getId();
        if (isset($this->_viewConfigs[$key])) {
            return $this->_viewConfigs[$key];
        }

        $configFiles = $this->_moduleReader->getModuleConfigurationFiles(Mage_Core_Model_Theme::FILENAME_VIEW_CONFIG);
        $themeConfigFile = $currentTheme->getCustomViewConfigPath();
        if (empty($themeConfigFile) || !$this->_filesystem->has($themeConfigFile)) {
            $themeConfigFile = $this->getFilename(Mage_Core_Model_Theme::FILENAME_VIEW_CONFIG, $params);
        }
        if ($themeConfigFile && $this->_filesystem->has($themeConfigFile)) {
            $configFiles[] = $themeConfigFile;
        }
        $config = new Magento_Config_View($configFiles);

        $this->_viewConfigs[$key] = $config;
        return $config;
    }
}
