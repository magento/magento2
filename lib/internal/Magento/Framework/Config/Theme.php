<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme configuration files handler
 */
namespace Magento\Framework\Config;

use Magento\Framework\Config\Composer\Package;

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
    public function __construct($configContent = null)
    {
        $this->_data = $this->_extractData($configContent);
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
     * Extract configuration data from theme.xml
     *
     * @param string $configContent
     * @return array
     */
    protected function _extractData($configContent)
    {
        $data = [
            'title' => null,
            'media' => null,
            'parent' => null,
        ];

        if (!empty($configContent)) {
            $dom = new \DOMDocument();
            $dom->loadXML($configContent);
            // todo: validation of the document
            /** @var $themeNode \DOMElement */
            $themeNode = $dom->getElementsByTagName('theme')->item(0);
            $themeTitleNode = $themeNode->getElementsByTagName('title')->item(0);
            $data['title'] = $themeTitleNode ? $themeTitleNode->nodeValue : null;
            /** @var $mediaNode \DOMElement */
            $mediaNode = $themeNode->getElementsByTagName('media')->item(0);
            $previewImage = $mediaNode ? $mediaNode->getElementsByTagName('preview_image')->item(0)->nodeValue : '';
            $data['media']['preview_image'] = $previewImage;
            $themeParentNode = $themeNode->getElementsByTagName('parent')->item(0);
            $data['parent'] = $themeParentNode ? $themeParentNode->nodeValue : null;
        }

        return $data;
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
