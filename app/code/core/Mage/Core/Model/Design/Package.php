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


class Mage_Core_Model_Design_Package
{
    /**
     * Default design area
     */
    const DEFAULT_AREA = 'frontend';

    /**
     * Scope separator
     */
    const SCOPE_SEPARATOR = '::';

    /**#@+
     * Public directories prefix group
     */
    const PUBLIC_MERGE_DIR  = '_merged';
    const PUBLIC_MODULE_DIR = '_module';
    const PUBLIC_VIEW_DIR   = '_view';
    const PUBLIC_THEME_DIR  = '_theme';
    /**#@-*/

    /**#@+
     * Extensions group for static files
     */
    const CONTENT_TYPE_CSS = 'css';
    const CONTENT_TYPE_JS  = 'js';
    /**#@-*/

    /**#@+
     * Protected extensions group for publication mechanism
     */
    const CONTENT_TYPE_PHP   = 'php';
    const CONTENT_TYPE_PHTML = 'phtml';
    const CONTENT_TYPE_XML   = 'xml';
    /**#@-*/

    /**
     * Published file cache storage tag
     */
    const PUBLIC_CACHE_TAG = 'design_public';

    /**#@+
     * Common node path to theme design configuration
     */
    const XML_PATH_THEME    = 'design/theme/full_name';
    const XML_PATH_THEME_ID = 'design/theme/theme_id';
    /**#@-*/

    /**
     * Path to configuration node for file duplication
     */
    const XML_PATH_ALLOW_DUPLICATION = 'default/design/theme/allow_view_files_duplication';

    /**
     * PCRE that matches non-absolute URLs in CSS content
     */
    const REGEX_CSS_RELATIVE_URLS
        = '#url\s*\(\s*(?(?=\'|").)(?!http\://|https\://|/|data\:)(.+?)(?:[\#\?].*?|[\'"])?\s*\)#';

    private static $_regexMatchCache      = array();
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
     * @var string
     */
    protected $_callbackFileDir;

    /**
     * List of theme configuration objects per area
     *
     * @var array
     */
    protected $_themeConfigs = array();

    /**
     * List of view configuration objects per theme
     *
     * @var array
     */
    protected $_viewConfigs = array();

    /**
     * Published file cache storage
     *
     * @var array
     */
    protected $_publicCache = array();

    /**
     * Array of fallback model, controlling rules of fallback and inheritance for appropriate
     * area, package, theme, locale
     *
     * @var array
     */
    protected $_fallback = array();

    /**
     * Array of theme model used for fallback mechanism
     *
     * @var array
     */
    protected $_themes = array();

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
        if (isset($this->_themes[$themeId])) {
            return $this->_themes[$themeId];
        }

        if (is_numeric($themeId)) {
            $themeModel = clone $this->getDesignTheme();
            $themeModel->load($themeId);
        } else {
            /** @var $collection Mage_Core_Model_Resource_Theme_Collection */
            $collection = $this->getDesignTheme()->getCollection();
            $themeModel = $collection->getThemeByFullPath($area . '/' . $themeId);
        }
        $this->_themes[$themeId] = $themeModel;

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
    public function getConfigurationDesignTheme($area = null, $params = array())
    {
        if (!$area) {
            $area = $this->getArea();
        }
        $useId = isset($params['useId']) ? $params['useId'] : true;
        $store = isset($params['store']) ? $params['store'] : null;

        $designTheme = (string)Mage::getStoreConfig($this->getConfigPathByArea($area, $useId), $store);
        if (empty($designTheme)) {
            $designTheme = (string)Mage::getConfig()->getNode($this->getConfigPathByArea($area, $useId));
        }
        return $designTheme;
    }


    /**
     * Get configuration xml path to current theme for area
     *
     * @param string $area
     * @param bool $useId
     * @return string
     */
    public function getConfigPathByArea($area, $useId = true)
    {
        $xmlPath = $useId ? self::XML_PATH_THEME_ID : self::XML_PATH_THEME;
        if ($area !== self::DEFAULT_AREA) {
            $xmlPath = $area . '/' . $xmlPath;
        }
        return $xmlPath;
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
            $params['themeModel'] = $this->_getLoadDesignTheme($params['themeId']);
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
        return  $this->_getFallback($params)->getFile($file, $params['module']);
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
        return $this->_getFallback($params)->getLocaleFile($file);
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
        return $this->_getFallback($params)->getViewFile($file, $params['module']);
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
        if (preg_match('/\.\//', str_replace('\\', '/', $file))) {
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
     * Return most appropriate model to perform fallback
     *
     * @param array $params
     * @return Mage_Core_Model_Design_FallbackInterface
     */
    protected function _getFallback($params)
    {
        $cacheKey = "{$params['area']}|{$params['themeModel']->getCacheKey()}|{$params['locale']}";
        if (!isset($this->_fallback[$cacheKey])) {
            $params['canSaveMap'] = (bool) (string) Mage::app()->getConfig()
                ->getNode('global/dev/design_fallback/allow_map_update');
            $params['mapDir'] = Mage::getConfig()->getTempVarDir() . '/maps/fallback';
            $params['baseDir'] = Mage::getBaseDir();

            $model = $this->_isDeveloperMode() ?
                'Mage_Core_Model_Design_Fallback' :
                'Mage_Core_Model_Design_Fallback_CachingProxy';
            $this->_fallback[$cacheKey] = Mage::getModel($model, array('data' => $params));
        }
        return $this->_fallback[$cacheKey];
    }

    /**
     * Return whether developer mode is turned on
     *
     * @return bool
     */
    protected function _isDeveloperMode()
    {
        return Mage::getIsDeveloperMode();
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
     */
    public function cleanMergedJsCss()
    {
        $dir = $this->_buildPublicViewFilename(self::PUBLIC_MERGE_DIR);
        $result = Varien_Io_File::rmdirRecursive($dir);
        $result = $result && Mage::helper('Mage_Core_Helper_File_Storage_Database')->deleteFolder($dir);
        return $result;
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
        /* Identify public file */
        $publicFile = $this->_publishViewFile($file, $params);
        /* Build url to public file */
        if (Mage::helper('Mage_Core_Helper_Data')->isStaticFilesSigned()) {
            $fileMTime = filemtime($publicFile);
            $url = $this->_getPublicFileUrl($publicFile, $isSecure);
            $url .= '?' . $fileMTime;
        } else {
            $url = $this->_getPublicFileUrl($publicFile, $isSecure);
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
    protected function _getPublicFileUrl($file, $isSecure = null)
    {
        $publicDirUrlTypes = array(
            Mage_Core_Model_Store::URL_TYPE_THEME  => Mage::getBaseDir('media') . DS . 'theme',
            Mage_Core_Model_Store::URL_TYPE_JS    => Mage::getBaseDir('js'),
        );
        foreach ($publicDirUrlTypes as $publicUrlType => $publicDir) {
            $publicDir .= DS;
            if (strpos($file, $publicDir) !== 0) {
                continue;
            }
            $url = str_replace($publicDir, '', $file);
            $url = str_replace(DS, '/' , $url);
            $url = Mage::getBaseUrl($publicUrlType, $isSecure) . $url;
            return $url;
        }
        throw new Magento_Exception(
            "Cannot build URL for the file '$file' because it does not reside in a public directory."
        );
    }

    /**
     * Get URLs to CSS files optimized based on configuration settings
     *
     * @param array $files
     * @return array
     */
    public function getOptimalCssUrls($files)
    {
        return $this->_getOptimalUrls(
            $files,
            self::CONTENT_TYPE_CSS,
            Mage::getStoreConfigFlag('dev/css/merge_css_files')
        );
    }

    /**
     * Get URLs to JS files optimized based on configuration settings
     *
     * @param array $files
     * @return array
     */
    public function getOptimalJsUrls($files)
    {
        return $this->_getOptimalUrls(
            $files,
            self::CONTENT_TYPE_JS,
            Mage::getStoreConfigFlag('dev/js/merge_files')
        );
    }

    /**
     * Prepare urls to files based on files type and merging option value
     *
     * @param array $files
     * @param string $type
     * @param bool $doMerge
     * @return array
     */
    protected function _getOptimalUrls($files, $type, $doMerge)
    {
        $urls = array();
        if ($doMerge && count($files) > 1) {
            $file = $this->_mergeFiles($files, $type);
            if (Mage::helper('Mage_Core_Helper_Data')->isStaticFilesSigned()) {
                $fileMTime = filemtime($file);
                $urls[] = $this->_getPublicFileUrl($file) . '?' . $fileMTime;
            } else {
                $urls[] = $this->_getPublicFileUrl($file);
            }
        } else {
            foreach ($files as $file) {
                $urls[] = $this->getViewFileUrl($file);
            }
        }
        return $urls;
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
        $themeFile = $this->_extractScope($themeFile, $params);
        $sourcePath = $this->getViewFile($themeFile, $params);

        $minifiedSourcePath = $this->_minifiedPathForStaticFiles($sourcePath);
        if ($minifiedSourcePath && !Mage::getIsDeveloperMode() && file_exists($minifiedSourcePath)) {
            $sourcePath = $minifiedSourcePath;
            $themeFile = $this->_minifiedPathForStaticFiles($themeFile);
        }

        if (!file_exists($sourcePath)) {
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
            $this->_setPublicFileIntoCache($themeFile, $params, $targetPath);
        }
        $targetPath = $this->_buildPublicViewFilename($targetPath);

        /* Validate whether file needs to be published */
        if ($this->_getExtension($themeFile) == self::CONTENT_TYPE_CSS) {
            $cssContent = $this->_getPublicCssContent($sourcePath, dirname($targetPath), $themeFile, $params);
        }

        $fileMTime = filemtime($sourcePath);
        if (!file_exists($targetPath) || $fileMTime != filemtime($targetPath)) {
            $publicDir = dirname($targetPath);
            if (!is_dir($publicDir)) {
                mkdir($publicDir, 0777, true);
            }

            if (isset($cssContent)) {
                file_put_contents($targetPath, $cssContent);
                touch($targetPath, $fileMTime);
            } elseif (is_file($sourcePath)) {
                copy($sourcePath, $targetPath);
                touch($targetPath, $fileMTime);
            } elseif (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }
        }

        $this->_getFallback($params)->notifyViewFilePublished($targetPath, $themeFile, $params['module']);
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
        $jsPath = Mage::getBaseDir('js') . DS;
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

        return $this->_isDeveloperMode() && $this->_getExtension($filePath) == self::CONTENT_TYPE_CSS;
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
        return Mage::getBaseDir('media') . DS . 'theme';
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
        } elseif($params['themeModel']->getId()) {
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
        $designDir = Mage::getBaseDir('design') . DS;
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
        $content = file_get_contents($filePath);
        $relativeUrls = $this->_extractCssRelativeUrls($content);
        foreach ($relativeUrls as $urlNotation => $fileUrl) {
            $relatedFilePathPublic = $this->_publishRelatedViewFile($fileUrl, $filePath, $fileName, $params);
            $fileUrlNew = basename($relatedFilePathPublic);
            $offset = $this->_getFilesOffset($relatedFilePathPublic, $publicDir);
            if ($offset) {
                $fileUrlNew = $this->_canonize($offset . '/' . $fileUrlNew, true);
            }
            $urlNotationNew = str_replace($fileUrl, $fileUrlNew, $urlNotation);
            $content = str_replace($urlNotation, $urlNotationNew, $content);
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
            $relativeThemeFile = $fileUrl;
        } else {
            /* Check if module file overridden on theme level based on _module property and file path */
            if ($params['module'] && strpos($parentFilePath, Mage::getBaseDir('design')) === 0) {
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
     * Merge files, located under the same folder, into one and return file name of merged file
     *
     * @param array $files list of names relative to the same folder
     * @param string $contentType
     * @return string
     * @throws Magento_Exception if not existing file requested for merge
     */
    protected function _mergeFiles($files, $contentType)
    {
        $filesToMerge = array();
        $mergedFile = array();
        $jsDir = Mage::getBaseDir('js');
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
            $filesMTimeData .= filemtime($file);
        }
        if (file_exists($mergedFile) && file_exists($mergedMTimeFile)
            && ($filesMTimeData == file_get_contents($mergedMTimeFile))
        ) {
            return $mergedFile;
        }
        if (!is_dir(dirname($mergedFile))) {
            mkdir(dirname($mergedFile), 0777, true);
        }

        $result = array();
        foreach ($filesToMerge as $file) {
            if (!file_exists($file)) {
                throw new Magento_Exception("Unable to locate file '{$file}' for merging.");
            }
            $content = file_get_contents($file);
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
        file_put_contents($mergedFile, $result, LOCK_EX);
        file_put_contents($mergedMTimeFile, $filesMTimeData, LOCK_EX);
        return $mergedFile;
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
     * Get hash key for requested file and parameters
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    protected function _getRequestedFileKey($file, $params)
    {
        ksort($params);
        return md5($this->_getRequestedFileCacheKey($params) . '|' . $file);
    }

    /**
     * Get cache key for parameters
     *
     * @param array $params
     * @return string
     */
    protected function _getRequestedFileCacheKey($params)
    {
        return implode('|', array($params['area'], $params['themeModel']->getId(), $params['locale']));
    }

    /**
     * Save published file path in cache storage
     *
     * @param string $file
     * @param array $params
     * @param string $publicFile
     */
    protected function _setPublicFileIntoCache($file, $params, $publicFile)
    {
        $cacheKey = $this->_getRequestedFileCacheKey($params);
        $this->_loadPublicCache($cacheKey);
        $fileKey = $this->_getRequestedFileKey($file, $params);
        $this->_publicCache[$cacheKey][$fileKey] = $publicFile;
        Mage::app()->saveCache(serialize($this->_publicCache[$cacheKey]), $cacheKey, array(self::PUBLIC_CACHE_TAG));
    }

    /**
     * Load published file cache storage from cache
     *
     * @param string $cacheKey
     */
    protected function _loadPublicCache($cacheKey)
    {
        if (!isset($this->_publicCache[$cacheKey])) {
            if ($cache = Mage::app()->loadCache($cacheKey)) {
                $this->_publicCache[$cacheKey] = unserialize($cache);
            } else {
                $this->_publicCache[$cacheKey] = array();
            }
        }
    }

    /**
     * Render view config object for current package and theme
     *
     * @return Magento_Config_View
     */
    public function getViewConfig()
    {
        $key = $this->getDesignTheme()->getId();
        if (isset($this->_viewConfigs[$key])) {
            return $this->_viewConfigs[$key];
        }

        $configFiles = Mage::getConfig()->getModuleConfigurationFiles('view.xml');
        $themeConfigFile = $this->getFilename('view.xml', array());
        if (file_exists($themeConfigFile)) {
            $configFiles[] = $themeConfigFile;
        }
        $config = new Magento_Config_View($configFiles);

        $this->_viewConfigs[$key] = $config;
        return $config;
    }
}
