<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme configuration files handler
 */
namespace Magento\Framework\Config;

/**
 * @api
 * @since 2.0.0
 */
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
     * @since 2.0.0
     */
    protected $_data;

    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     * @since 2.0.0
     */
    protected $urnResolver;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @param string $configContent
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\Dom\UrnResolver $urnResolver,
        $configContent = null
    ) {
        $this->urnResolver = $urnResolver;
        $this->_data = $this->_extractData($configContent);
    }

    /**
     * Get absolute path to theme.xsd
     *
     * @return string
     * @since 2.0.0
     */
    public function getSchemaFile()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Config/etc/theme.xsd');
    }

    /**
     * Extract configuration data from theme.xml
     *
     * @param string $configContent
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getThemeTitle()
    {
        return $this->_data['title'];
    }

    /**
     * Get theme media data
     *
     * @return array
     * @since 2.0.0
     */
    public function getMedia()
    {
        return $this->_data['media'];
    }

    /**
     * Retrieve a parent theme code
     *
     * @return array|null
     * @since 2.0.0
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
