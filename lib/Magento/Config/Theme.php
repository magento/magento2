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
 * @category    Magento
 * @package     Framework
 * @subpackage  Config
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme configuration files handler
 */
class Magento_Config_Theme extends Magento_Config_XmlAbstract
{
    /**
     * Get absolute path to theme.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/theme.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param DOMDocument $dom
     * @return array
     */
    protected function _extractData(DOMDocument $dom)
    {
        $result = array();
        /** @var $packageNode DOMElement */
        foreach ($dom->childNodes->item(0)/*root*/->childNodes as $packageNode) {
            $packageCode = $packageNode->getAttribute('code');
            $packageTitle = $packageNode->getElementsByTagName('title')->item(0)->nodeValue;
            /** @var $themeNode DOMElement */
            foreach ($packageNode->getElementsByTagName('theme') as $themeNode) {
                /** @var $requirementsNode DOMElement */
                $requirementsNode = $themeNode->getElementsByTagName('requirements')->item(0);
                /** @var $versionNode DOMElement */
                $versionNode = $requirementsNode->getElementsByTagName('magento_version')->item(0);

                $themeCode = $themeNode->getAttribute('code');
                $themeParentCode = $themeNode->getAttribute('parent') ?: null;
                $themeTitle = $themeNode->getElementsByTagName('title')->item(0)->nodeValue;
                $versionFrom = $versionNode->getAttribute('from');
                $versionTo = $versionNode->getAttribute('to');

                $result[$packageCode]['title'] = $packageTitle;
                $result[$packageCode]['themes'][$themeCode] = array(
                    'title' => $themeTitle,
                    'parent' => $themeParentCode,
                    'requirements' => array(
                        'magento_version' => array(
                            'from' => $versionFrom,
                            'to'   => $versionTo,
                        ),
                    ),
                );
            }
        }
        return $result;
    }

    /**
     * Get title for specified package code
     *
     * @param string $package
     * @return string
     * @throws Magento_Exception
     */
    public function getPackageTitle($package)
    {
        $this->_ensurePackageExists($package);
        return $this->_data[$package]['title'];
    }

    /**
     * Get title for specified theme and package code
     *
     * @param string $package
     * @param string $theme
     * @return string
     */
    public function getThemeTitle($package, $theme)
    {
        $this->_ensureThemeExists($package, $theme);
        return $this->_data[$package]['themes'][$theme]['title'];
    }

    /**
     * Retrieve a parent theme code
     *
     * @param string $package
     * @param string $theme
     * @return string|null
     */
    public function getParentTheme($package, $theme)
    {
        $this->_ensureThemeExists($package, $theme);
        return $this->_data[$package]['themes'][$theme]['parent'];
    }

    /**
     * Getter for Magento versions compatible with theme
     *
     * return an array with 'from' and 'to' keys
     *
     * @param string $package
     * @param string $theme
     * @return array
     */
    public function getCompatibleVersions($package, $theme)
    {
        $this->_ensureThemeExists($package, $theme);
        return $this->_data[$package]['themes'][$theme]['requirements']['magento_version'];
    }

    /**
     * Check whether a package is declared in the configuration
     *
     * @param string $package
     * @throws Magento_Exception
     */
    protected function _ensurePackageExists($package)
    {
        if (!isset($this->_data[$package])) {
            throw new Magento_Exception('Unknown design package "' . $package . '".');
        }
    }

    /**
     * Check whether a theme exists in a design package
     *
     * @param string $package
     * @param string $theme
     * @throws Magento_Exception
     */
    protected function _ensureThemeExists($package, $theme)
    {
        if (!isset($this->_data[$package]['themes'][$theme])) {
            throw new Magento_Exception('Unknown theme "' . $theme . '" in "' . $package . '" package.');
        }
    }

    /**
     * Get initial XML of a valid document
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?><design></design>';
    }

    /**
     * Design packages are unique by code. Themes are unique by code.
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array('/design/package' => 'code', '/design/package/theme' => 'code');
    }
}
