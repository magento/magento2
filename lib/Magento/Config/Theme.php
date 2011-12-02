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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
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
     * Get title for specified package code
     *
     * @param string $code
     * @return string|false
     */
    public function getPackageTitle($code)
    {
        return $this->_getScalarNodeValue("/design/package[@code='{$code}']/title");
    }

    /**
     * Get title for specified theme and package code
     *
     * @param string $themeCode
     * @param string $packageCode
     * @return string|false
     */
    public function getThemeTitle($themeCode, $packageCode)
    {
        return $this->_getScalarNodeValue("/design/package[@code='{$packageCode}']/theme[@code='{$themeCode}']/title");
    }

    /**
     * Treat provided xPath query as a reference to fully qualified element with scalar value
     *
     * @param string $xPathQuery
     * @return string|false
     */
    protected function _getScalarNodeValue($xPathQuery)
    {
        $xPath = new DOMXPath($this->_dom);
        /** @var DOMElement $element */
        foreach ($xPath->query($xPathQuery) as $element) {
            return (string)$element->nodeValue;
        }
        return false;
    }

    /**
     * Getter for Magento versions compatible with theme
     *
     * return an array with 'from' and 'to' keys
     *
     * @param string $package
     * @param string $theme
     * @throw Exception an exception in case of unknown theme
     * @return array
     */
    public function getCompatibleVersions($package, $theme)
    {
        $xPath = new DOMXPath($this->_dom);
        $version = $xPath
            ->query("/design/package[@code='{$package}']/theme[@code='{$theme}']/requirements/magento_version")
            ->item(0);
        if (!$version) {
            throw new Exception('Unknown theme "' . $theme . '" in "' . $package . '" package.');
        }
        $result = array(
            'from'  => $version->getAttribute('from'),
            'to'    => $version->getAttribute('to')
        );
        return $result;
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
