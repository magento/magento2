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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_Model_Design_Package
{
    const DEFAULT_AREA    = 'frontend';
    const DEFAULT_PACKAGE = 'default';
    const DEFAULT_THEME   = 'default';

    const SCOPE_SEPARATOR = '::';

    const PUBLIC_MERGE_DIR  = '_merged';
    const PUBLIC_MODULE_DIR = '_module';

    const CONTENT_TYPE_CSS = 'css';
    const CONTENT_TYPE_JS  = 'js';

    /**
     * The name of the default skins in the context of a theme
     */
    const DEFAULT_SKIN_NAME = 'default';

    /**
     * The name of the default theme in the context of a package
     */
    const DEFAULT_THEME_NAME = 'default';

    /**
     * Published file cache storage tag
     */
    const PUBLIC_CACHE_TAG = 'design_public';

    const XML_PATH_THEME = 'design/theme/full_name';

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
     * Package name
     *
     * @var string
     */
    protected $_name;

    /**
     * Package theme
     *
     * @var string
     */
    protected $_theme;

    /**
     * Package skin
     *
     * @var string
     */
    protected $_skin;

    /**
     * Package root directory
     *
     * @var string
     */
    protected $_rootDir;

    /**
     * Directory of the css file
     * Using only to transmit additional parametr in callback functions
     * @var string
     */
    protected $_callbackFileDir;

    protected $_config = null;

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
     * Published file cache storages
     *
     * @var array
     */
    protected $_publicCache = array();

    /**
     * Array of fallback model, controlling rules of fallback and inheritance for appropriate
     * area, package, theme, skin, locale
     *
     * @var array
     */
    protected $_fallback = array();

    /**
     * Set package area
     *
     * @param  string $area
     * @return Mage_Core_Model_Design_Package
     */
    public function setArea($area)
    {
        $this->_area = $area;
        return $this;
    }

    /**
     * Retrieve package area
     *
     * @return unknown
     */
    public function getArea()
    {
        if (is_null($this->_area)) {
            $this->_area = self::DEFAULT_AREA;
        }
        return $this->_area;
    }

    /**
     * Retrieve package name
     *
     * @return string
     */
    public function getPackageName()
    {
        if (!$this->_name) {
            $this->_name = self::DEFAULT_PACKAGE;
        }
        return $this->_name;
    }

    /**
     * Package theme getter
     *
     * @return string
     */
    public function getTheme()
    {
        if (!$this->_theme) {
            $this->_theme = self::DEFAULT_THEME;
        }

        return $this->_theme;
    }

    /**
     * Skin getter
     *
     * @return string
     */
    public function getSkin()
    {
        if (!$this->_skin) {
            $this->_skin = self::DEFAULT_SKIN_NAME;
        }
        return $this->_skin;
    }

    /**
     * Set package, theme and skin for current area
     *
     * $themePath name must contain package, theme and skin names separated by "/"
     *
     * @param string $themePath
     * @param string $area
     * @return Mage_Core_Model_Design_Package
     */
    public function setDesignTheme($themePath, $area = null)
    {
        $parts = explode('/', $themePath);
        if (3 !== count($parts)) {
            Mage::throwException(
                Mage::helper('Mage_Core_Helper_Data')->__('Invalid fully qualified design name: "%s".', $themePath)
            );
        }
        list($package, $theme, $skin) = $parts;
        if ($area) {
            $this->setArea($area);
        }

        $this->_name = $package;
        $this->_theme = $theme;
        $this->_skin = $skin;
        return $this;
    }

    /**
     * Design theme full name getter
     *
     * @return string
     */
    public function getDesignTheme()
    {
        return $this->getPackageName() . '/' . $this->getTheme() . '/' . $this->getSkin();
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
        if (empty($params['package'])) {
            $params['package'] = $this->getPackageName();
        }
        if (!array_key_exists('theme', $params)) {
            $params['theme'] = $this->getTheme();
        }
        if (!array_key_exists('skin', $params)) {
            $params['skin'] = $this->getSkin();
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
     * Find a skin file using fallback mechanism
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getSkinFile($file, array $params = array())
    {
        $file = $this->_extractScope($file, $params);
        $this->_updateParamDefaults($params);
        return $this->_getFallback($params)->getSkinFile($file, $params['module']);
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
        $cacheKey = "{$params['area']}|{$params['package']}|{$params['theme']}|{$params['skin']}|{$params['locale']}";
        if (!isset($this->_fallback[$cacheKey])) {
            $params['canSaveMap'] = (bool) (string) Mage::app()->getConfig()
                ->getNode('global/dev/design_fallback/allow_map_update');
            $params['mapDir'] = Mage::getConfig()->getTempVarDir() . '/maps/fallback';
            $params['baseDir'] = Mage::getBaseDir();

            $model = $this->_isDeveloperMode() ?
                'Mage_Core_Model_Design_Fallback' :
                'Mage_Core_Model_Design_Fallback_CachingProxy';
            $this->_fallback[$cacheKey] = Mage::getModel($model, $params);
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
     * Design packages list getter
     *
     * @return array
     */
    public function getPackageList()
    {
        $directory = Mage::getBaseDir('design') . DS . 'frontend';
        return $this->_listDirectories($directory);
    }

    /**
     * Retrieve the list of themes available in the system.
     * Results are grouped by packages themes belong to, if the optional 'package' argument is omitted.
     *
     * @param string|null $package
     * @return array
     */
    public function getThemeList($package = null)
    {
        $result = array();

        if (is_null($package)){
            foreach ($this->getPackageList() as $package){
                $result[$package] = $this->getThemeList($package);
            }
        } else {
            $directory = Mage::getBaseDir('design') . DS . 'frontend' . DS . $package;
            $result = $this->_listDirectories($directory);
        }

        return $result;
    }

    /**
     * Directories lister utility method
     *
     * @param string $path
     * @param string|false $fullPath
     * @return array
     */
    private function _listDirectories($path, $fullPath = false)
    {
        $result = array();
        $dir = opendir($path);
        if ($dir) {
            while ($entry = readdir($dir)) {
                if (substr($entry, 0, 1) == '.' || !is_dir($path . DS . $entry)){
                    continue;
                }
                if ($fullPath) {
                    $entry = $path . DS . $entry;
                }
                $result[] = $entry;
            }
            unset($entry);
            closedir($dir);
        }

        return $result;
    }

    /**
     * Return package name based on design exception rules
     *
     * @param array $rules - design exception rules
     * @param string $regexpsConfigPath
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
     * @return  bool
     */
    public function cleanMergedJsCss()
    {
        $dir = $this->_buildPublicSkinFilename(self::PUBLIC_MERGE_DIR);
        $result = Varien_Io_File::rmdirRecursive($dir);
        $result = $result && Mage::helper('Mage_Core_Helper_File_Storage_Database')->deleteFolder($dir);
        return $result;
    }

    /**
     * Get url to file base on skin file identifier.
     * Publishes file there, if needed.
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getSkinUrl($file, array $params = array())
    {
        $isSecure = isset($params['_secure']) ? (bool) $params['_secure'] : null;
        unset($params['_secure']);
        $this->_updateParamDefaults($params);
        /* Identify public file */
        $publicFile = $this->_publishSkinFile($file, $params);
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
            Mage_Core_Model_Store::URL_TYPE_SKIN => Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 'skin',
            Mage_Core_Model_Store::URL_TYPE_JS    => Mage::getBaseDir('js'),
        );
        foreach ($publicDirUrlTypes as $publicUrlType => $publicDir) {
            $publicDir .= DIRECTORY_SEPARATOR;
            if (strpos($file, $publicDir) !== 0) {
                continue;
            }
            $url = str_replace($publicDir, '', $file);
            $url = str_replace(DIRECTORY_SEPARATOR, '/' , $url);
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
                $urls[] = $this->getSkinUrl($file);
            }
        }
        return $urls;
    }

    /**
     * Check, if requested skin file has public access, and move it to public folder, if the file has no public access
     *
     * @param  string $skinFile
     * @param  array $params
     * @return string
     * @throws Magento_Exception
     */
    protected function _publishSkinFile($skinFile, $params)
    {
        $skinFile = $this->_extractScope($skinFile, $params);

        $file = $this->getSkinFile($skinFile, $params);

        $dotPosition = strrpos($skinFile, '.');
        $extension = strtolower(substr($skinFile, $dotPosition + 1));
        $staticContentTypes = array(
            Mage_Core_Model_Design_Package::CONTENT_TYPE_JS,
            Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS,
        );
        if (!Mage::getIsDeveloperMode() && !empty($extension) &&
            in_array($extension, $staticContentTypes)
        ) {
            $minifiedPath = str_replace('.' . $extension, '.min.' . $extension, $file);
            if (file_exists($minifiedPath)) {
                $file = $minifiedPath;
                $skinFile = str_replace('.' . $extension, '.min.' . $extension, $skinFile);
            }
        }

        if (!file_exists($file)) {
            throw new Magento_Exception("Unable to locate skin file '{$file}'.");
        }

        if (!$this->_needToProcessFile($file)) {
            return $file;
        }

        $isDuplicationAllowed = (string)Mage::getConfig()->getNode('default/design/theme/allow_skin_files_duplication');
        $isCssFile = ($extension === Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS);
        if ($isDuplicationAllowed || $isCssFile) {
            $publicFile = $this->_buildPublicSkinRedundantFilename($skinFile, $params);
        } else {
            $publicFile = $this->_buildPublicSkinSufficientFilename($file, $params);
            $this->_setPublicFileIntoCache($skinFile, $params, $publicFile);
        }

        $fileMTime = filemtime($file);

        /* Validate whether file needs to be published */
        if (!file_exists($publicFile) || $fileMTime != filemtime($publicFile)) {
            $publicDir = dirname($publicFile);
            if (!is_dir($publicDir)) {
                mkdir($publicDir, 0777, true);
            }

            /* Process relative urls for CSS files */
            if ($isCssFile) {
                $content = $this->_getPublicCssContent($file, dirname($publicFile), $skinFile, $params);
                file_put_contents($publicFile, $content);
            } else {
                if (is_file($file)) {
                    copy($file, $publicFile);
                } elseif (!is_dir($publicFile)) {
                    mkdir($publicFile, 0777, true);
                }
            }
            if (is_file($publicFile)) {
                touch($publicFile, $fileMTime);
            }
        } else if ($isCssFile) {
            // Trigger related skin files publication, if CSS file itself has not been changed
            $this->_getPublicCssContent($file, dirname($publicFile), $skinFile, $params);
        }

        $this->_getFallback($params)->notifySkinFilePublished($publicFile, $skinFile, $params['module']);
        return $publicFile;
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
        $jsPath = Mage::getBaseDir('js') . DIRECTORY_SEPARATOR;
        if (strncmp($filePath, $jsPath, strlen($jsPath)) === 0) {
            return false;
        }

        $skinPath = $this->getPublicSkinDir() . DIRECTORY_SEPARATOR;
        if (strncmp($filePath, $skinPath, strlen($skinPath)) !== 0) {
            return true;
        }

        return $this->_isDeveloperMode() && $this->_isCssFile($filePath);
    }

    /**
     * Check whether $file is a CSS-file
     *
     * @param string $file
     * @return bool
     */
    protected function _isCssFile($file)
    {
        return (bool) preg_match('/\.css$/', $file);
    }

    /**
     * Build path to file located in public folder
     *
     * @param string $file
     * @return string
     */
    protected function _buildPublicSkinFilename($file)
    {
        return $this->getPublicSkinDir() . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * Return directory for skin files publication
     *
     * @return string
     */
    public function getPublicSkinDir()
    {
        return Mage::getBaseDir('media')  . DIRECTORY_SEPARATOR . 'skin';
    }

    /**
     * Build public filename for a skin file that always includes area/package/theme/skin/locate parameters
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    protected function _buildPublicSkinRedundantFilename($file, array $params)
    {
        $publicFile = $params['area']
            . DIRECTORY_SEPARATOR . $params['package']
            . DIRECTORY_SEPARATOR . $params['theme']
            . DIRECTORY_SEPARATOR . $params['skin']
            . DIRECTORY_SEPARATOR . $params['locale']
            . ($params['module'] ? DIRECTORY_SEPARATOR . $params['module'] : '')
            . DIRECTORY_SEPARATOR . $file
        ;
        $publicFile = $this->_buildPublicSkinFilename($publicFile);
        return $publicFile;
    }

    /**
     * Build public filename for a skin file that sufficiently depends on the passed parameters
     *
     * @param string $filename
     * @param array $params
     * @return string
     */
    protected function _buildPublicSkinSufficientFilename($filename, array $params)
    {
        $designDir = Mage::getBaseDir('design') . DIRECTORY_SEPARATOR;
        if (0 === strpos($filename, $designDir)) {
            // theme file
            $publicFile = substr($filename, strlen($designDir));
        } else {
            // modular file
            $module = $params['module'];
            $moduleDir = Mage::getModuleDir('skin', $module) . DIRECTORY_SEPARATOR;
            $publicFile = substr($filename, strlen($moduleDir));
            $publicFile = self::PUBLIC_MODULE_DIR . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $publicFile;
        }
        $publicFile = $this->_buildPublicSkinFilename($publicFile);
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
            $relatedFilePathPublic = $this->_publishRelatedSkinFile($fileUrl, $filePath, $fileName, $params);
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
     * @param array $params theme/skin/module parameters array
     * @return string
     */
    protected function _publishRelatedSkinFile($fileUrl, $parentFilePath, $parentFileName, $params)
    {
        if (strpos($fileUrl, self::SCOPE_SEPARATOR)) {
            $relativeSkinFile = $fileUrl;
        } else {
            /* Check if module file overridden on theme level based on _module property and file path */
            if ($params['module'] && strpos($parentFilePath, Mage::getBaseDir('design')) === 0) {
                /* Add module directory to relative URL for canonization */
                $relativeSkinFile = dirname($params['module'] . DIRECTORY_SEPARATOR . $parentFileName)
                    . DIRECTORY_SEPARATOR . $fileUrl;
                $relativeSkinFile   = $this->_canonize($relativeSkinFile);
                if (strpos($relativeSkinFile, $params['module']) === 0) {
                    $relativeSkinFile = str_replace($params['module'], '', $relativeSkinFile);
                } else {
                    $params['module'] = false;
                }
            } else {
                $relativeSkinFile = $this->_canonize(dirname($parentFileName) . DIRECTORY_SEPARATOR . $fileUrl);
            }
        }
        return $this->_publishSkinFile($relativeSkinFile, $params);
    }

    /**
     * Canonize the specified filename
     *
     * Removes excessive "./" and "../" from the path.
     * Returns false, if cannot get rid of all "../"
     *
     * @param string $filename
     * @param bool $isRelative flag that identify that filename is relative
     * @throws Magento_Exception if file can't be canonized
     * @return string|false
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
     * @throws Magento_Exception if not existing file requested for merge
     * @return string
     */
    protected function _mergeFiles($files, $contentType)
    {
        $filesToMerge = array();
        $mergedFile = array();
        $jsDir = Mage::getBaseDir('js');
        $publicDir = $this->_buildPublicSkinFilename('');
        foreach ($files as $file) {
            $params = array();
            $this->_updateParamDefaults($params);
            $filesToMerge[$file] = $this->_publishSkinFile($file, $params);
            $mergedFile[] = str_replace('\\', '/', str_replace(array($jsDir, $publicDir), '', $filesToMerge[$file]));
        }
        $mergedFile = self::PUBLIC_MERGE_DIR . DIRECTORY_SEPARATOR . md5(implode('|', $mergedFile)) . ".{$contentType}";
        $mergedFile = $this->_buildPublicSkinFilename($mergedFile);
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
     *  app/design/frontend/default/default/skin/default/style.css
     *  pub/skin/frontend/default/default/skin/default/style.css
     *
     * Case 2: public file to public folder - $fileOffset = '../frontend/default/default/skin/default';
     *  pub/skin/frontend/default/default/skin/default/style.css -> img/empty.gif
     *  pub/skin/_merged/hash.css -> ../frontend/default/default/skin/default/img/empty.gif
     *
     * @throws Magento_Exception
     * @param string $originalFile path to original file
     * @param string $relocationDir path to directory where content will be relocated
     * @return string
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
        $offset = str_replace(DIRECTORY_SEPARATOR, '/', $offset);
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
            $result = implode("\n", $imports). "\n"
                . "/* Import directives above popped up. */\n"
                . $result
            ;
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
        return md5(implode('_', $params) . '_' . $file);
    }

    /**
     * Get cache key for parameters
     *
     * @param array $params
     * @return string
     */
    protected function _getRequestedFileCacheKey($params)
    {
        return $params['area'] . '/' . $params['package'] . '/' . $params['theme'] . '/'
            . $params['skin'] . '/' . $params['locale'];
    }

    /**
     * Save published file path in cache storage
     *
     * @param string $file
     * @param array $params
     * @param string $publicFile
     * @return void
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
     * @return void
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
     * Get the structure for area with all possible design combinations
     *
     * The format of the result is a multidimensional array with following structure
     * array (
     *     'package_name' => array (
     *         'theme_name' => array (
     *             'skin_name' => true
     *          )
     *     )
     * )
     *
     * @param string $area
     * @param bool $addInheritedSkins
     * @return array
     */
    public function getDesignEntitiesStructure($area, $addInheritedSkins = true)
    {
        $areaThemeConfig = $this->getThemeConfig($area);
        $areaStructure = $this->_getDesignEntitiesFilesystemStructure($area);

        foreach ($areaStructure as $packageName => &$themes) {
            foreach ($themes as $themeName => &$skins) {

                /**
                 * Join to theme inherited skins
                 */
                if ($addInheritedSkins) {
                    $currentPackage = $packageName;
                    $currentTheme = $themeName;
                    while ($inheritedPackageTheme = $areaThemeConfig->getParentTheme($currentPackage, $currentTheme)) {
                        list($inheritedPackage, $inheritedTheme) = $inheritedPackageTheme;
                        if (!isset($areaStructure[$inheritedPackage][$inheritedTheme])) {
                            break;
                        }
                        $areaStructure[$packageName][$themeName] = array_merge(
                            $areaStructure[$packageName][$themeName],
                            $areaStructure[$inheritedPackage][$inheritedTheme]
                        );
                        $currentPackage = $inheritedPackage;
                        $currentTheme = $inheritedTheme;
                    }
                }

                /**
                 * Delete themes without skins or sort skins
                 */
                if (empty($areaStructure[$packageName][$themeName])) {
                    unset($areaStructure[$packageName][$themeName]);
                } else {
                    ksort($skins);
                }
            }
            /**
             * Delete packages without themes or sort themes
             */
            if (empty($areaStructure[$packageName])) {
                unset($areaStructure[$packageName]);
            }
            ksort($themes);
        }

        ksort($areaStructure);
        return $areaStructure;
    }

    /**
     * Get entities file system structure of area
     *
     * The format of the result is a multidimensional array with following structure
     * array (
     *     'package_name' => array (
     *         'theme_name' => array (
     *             'skin_name' => true
     *          )
     *     )
     * )
     *
     * @param string $area
     * @return array
     */
    protected function _getDesignEntitiesFilesystemStructure($area)
    {
        $areaDirPath = Mage::app()->getConfig()->getOptions()->getDesignDir() . DIRECTORY_SEPARATOR . $area;
        $structure = array();

        $themePaths = glob($areaDirPath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        foreach ($themePaths as $themePath) {
            $themePath = str_replace(DIRECTORY_SEPARATOR, '/', $themePath);
            if (preg_match('/\/([^\/.]+)\/([^\/.]+)\/([^\/.]+)$/i', $themePath, $packageThemeMatches)) {
                list (, , $packageName, $themeName) = $packageThemeMatches;
                $structure[$packageName][$themeName] = array();
            } else {
                continue;
            }
            $skinPaths = glob($areaDirPath . DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR .
                $themeName . DIRECTORY_SEPARATOR . 'skin' . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
            foreach ($skinPaths as $skinPath) {
                $skinPath = str_replace(DIRECTORY_SEPARATOR, '/', $skinPath);
                if (preg_match('/\/([^\/.]+)$/i', $skinPath, $skinMatches)) {
                    $structure[$packageName][$themeName][$skinMatches[1]] = true;
                }
            }
        }

        return $structure;
    }

    /**
     * Get theme configuration for specified area
     *
     * @param string $area
     * @return Magento_Config_Theme
     */
    public function getThemeConfig($area)
    {
        if (isset($this->_themeConfigs[$area])) {
            return $this->_themeConfigs[$area];
        }
        $configFiles = glob(Mage::getBaseDir('design') . "/{$area}/*/*/theme.xml", GLOB_NOSORT);
        $config = new Magento_Config_Theme($configFiles);
        $this->_themeConfigs[$area] = $config;
        return $config;
    }

    /**
     * Check if the theme from the specified area is compatible with specific Magento version
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param string $version
     * @return bool
     */
    public function isThemeCompatible($area, $package, $theme, $version)
    {
        $versions = $this->getThemeConfig($area)->getCompatibleVersions($package, $theme);
        if (version_compare($version, $versions['from'], '>=')) {
            if ($versions['to'] == '*' || version_compare($version, $versions['from'], '<=')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Render view config object for current package and theme
     *
     * @return Magento_Config_View
     */
    public function getViewConfig()
    {
        $key = "{$this->_name}/{$this->_theme}";
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
