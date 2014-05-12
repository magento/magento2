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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme configuration files handler
 */
namespace Magento\Framework\Config;

class Theme
{
    /**
     * Is used for separation path of themes
     */
    const THEME_PATH_SEPARATOR = '/';

    /**
     * Data extracted from the configuration file
     *
     * @var array
     */
    protected $_data;

    /**
     * Constructor
     *
     * @param string $configContent
     */
    public function __construct($configContent)
    {
        $config = new \DOMDocument();
        $config->loadXML($configContent);
        // todo: validation of the document
        $this->_data = $this->_extractData($config);
    }

    /**
     * Get absolute path to theme.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/etc/theme.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     */
    protected function _extractData(\DOMDocument $dom)
    {
        /** @var $themeNode \DOMElement */
        $themeNode = $dom->getElementsByTagName('theme')->item(0);
        /** @var $mediaNode \DOMElement */
        $mediaNode = $themeNode->getElementsByTagName('media')->item(0);

        $themeVersionNode = $themeNode->getElementsByTagName('version')->item(0);
        $themeParentNode = $themeNode->getElementsByTagName('parent')->item(0);
        $themeTitleNode = $themeNode->getElementsByTagName('title')->item(0);
        $previewImage = $mediaNode ? $mediaNode->getElementsByTagName('preview_image')->item(0)->nodeValue : '';

        return array(
            'title' => $themeTitleNode->nodeValue,
            'parent' => $themeParentNode ? $themeParentNode->nodeValue : null,
            'version' => $themeVersionNode ? $themeVersionNode->nodeValue : null,
            'media' => array('preview_image' => $previewImage)
        );
    }

    /**
     * Get title for specified package code
     *
     * @return string
     */
    public function getThemeVersion()
    {
        return $this->_data['version'];
    }

    /**
     * Get title for specified theme and package code
     *
     * @return string
     */
    public function getThemeTitle()
    {
        return $this->_data['title'];
    }

    /**
     * Get theme media data
     *
     * @return array
     */
    public function getMedia()
    {
        return $this->_data['media'];
    }

    /**
     * Retrieve a parent theme code
     *
     * @return array|null
     */
    public function getParentTheme()
    {
        $parentTheme = $this->_data['parent'];
        if (!$parentTheme) {
            return null;
        }
        return explode(self::THEME_PATH_SEPARATOR, $parentTheme);
    }
}
