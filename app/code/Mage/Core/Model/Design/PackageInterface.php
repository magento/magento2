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

interface Mage_Core_Model_Design_PackageInterface
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
     * The name of the default theme in the context of a package
     */
    const DEFAULT_THEME_NAME = 'default';

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
     * Path to configuration node that indicates how to materialize view files: with or without "duplication"
     */
    const XML_PATH_ALLOW_DUPLICATION = 'global/design/theme/allow_view_files_duplication';

    /**
     * PCRE that matches non-absolute URLs in CSS content
     */
    const REGEX_CSS_RELATIVE_URLS
        = '#url\s*\(\s*(?(?=\'|").)(?!http\://|https\://|/|data\:)(.+?)(?:[\#\?].*?|[\'"])?\s*\)#';

    /**
     * Set package area
     *
     * @param string $area
     * @return Mage_Core_Model_Design_PackageInterface
     */
    public function setArea($area);

    /**
     * Retrieve package area
     *
     * @return string
     */
    public function getArea();


    /**
     * Set theme path
     *
     * @param Mage_Core_Model_Theme|int|string $theme
     * @param string $area
     * @return Mage_Core_Model_Design_PackageInterface
     */
    public function setDesignTheme($theme, $area = null);


    /**
     * Get default theme which declared in configuration
     *
     * @param string $area
     * @param array $params
     * @return string|int
     */
    public function getConfigurationDesignTheme($area = null, array $params = array());

    /**
     * Set default design theme
     *
     * @return Mage_Core_Model_Design_PackageInterface
     */
    public function setDefaultDesignTheme();

    /**
     * Design theme model getter
     *
     * @return Mage_Core_Model_Theme
     */
    public function getDesignTheme();

    /**
     * Get existing file name with fallback to default
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getFilename($file, array $params = array());

    /**
     * Get a locale file
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getLocaleFileName($file, array $params = array());

    /**
     * Find a view file using fallback mechanism
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getViewFile($file, array $params = array());

    /**
     * Remove all merged js/css files
     *
     * @return bool
     */
    public function cleanMergedJsCss();

    /**
     * Get url to file base on theme file identifier.
     * Publishes file there, if needed.
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($file, array $params = array());

    /**
     * Get url to public file
     *
     * @param string $file
     * @param bool|null $isSecure
     * @return string
     * @throws Magento_Exception
     */
    public function getPublicFileUrl($file, $isSecure = null);

    /**
     * Return directory for theme files publication
     *
     * @return string
     */
    public function getPublicDir();

    /**
     * Return whether view files merging is allowed or not
     *
     * @return bool
     */
    public function isMergingViewFilesAllowed();

    /**
     * Merge files, located under the same folder, into one and return file name of merged file
     *
     * @param array $files list of names relative to the same folder
     * @param string $contentType
     * @return string
     * @throws Magento_Exception if not existing file requested for merge
     */
    public function mergeFiles($files, $contentType);

    /**
     * Render view config object for current package and theme
     *
     * @return Magento_Config_View
     */
    public function getViewConfig();
}
