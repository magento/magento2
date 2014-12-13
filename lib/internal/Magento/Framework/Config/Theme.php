<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @param string $composerContent
     */
    public function __construct(
        $configContent = null,
        $composerContent = null
    ) {
        $this->_data = $this->_extractData($configContent, $composerContent);
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
     * Extract configuration data from theme.xml and composer.json
     *
     * @param string $configContent
     * @param string $composerContent
     * @return array
     */
    protected function _extractData($configContent, $composerContent)
    {
        $data = [
            'version' => null,
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
            $data['title'] = $themeNode->getElementsByTagName('title')->item(0)->nodeValue;
            /** @var $mediaNode \DOMElement */
            $mediaNode = $themeNode->getElementsByTagName('media')->item(0);
            $previewImage = $mediaNode ? $mediaNode->getElementsByTagName('preview_image')->item(0)->nodeValue : '';
            $data['media']['preview_image'] = $previewImage;
        }

        if (!empty($composerContent)) {
            $json = json_decode($composerContent);
            $package = new Package($json);
            $data['version'] = $package->get('version');
            $parents = (array)$package->get('require', '/.+\/theme-/');
            $parents = empty($parents) ? null : array_keys($parents);
            $data['parent'] = empty($parents) ? null : array_shift($parents);
        }

        return $data;
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
        $parent = $this->parseThemeName($parentTheme);
        return [ucfirst($parent['vendor']), $parent['name']];
    }

    /**
     * Parse theme name
     *
     * @param string $themeName
     * @return array|null Return array if theme name is in the right format, otherwise null is returned, for example:
     *   [
     *     'vendor' => 'magento',
     *     'area' => 'frontend',
     *     'name' => 'luma'
     *   ]
     */
    private function parseThemeName($themeName)
    {
        preg_match('/(?<vendor>.+)\/theme-(?<area>.+)-(?<name>.+)/', $themeName, $matches);
        return [
            'vendor' => $matches['vendor'],
            'area' => $matches['area'],
            'name' => $matches['name'],
        ];
    }
}
