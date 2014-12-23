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
    public function __construct($configContent = null, $composerContent = null)
    {
        $xmlData = $this->getValuesFromXmlConfig($configContent);
        $composerData = $this->getValuesFromComposerConfig($composerContent);
        $this->validateConfigurationData($xmlData, $composerData);

        $this->_data = array_merge($this->getArrayWithAllConfigKeysSetToNull(), $xmlData, $composerData);
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
     * Extract configuration data from theme.xml and composer.json content
     *
     * @param string $configContent
     * @param string $composerContent
     * @return array
     */
    protected function extractData($configContent, $composerContent)
    {
        $xmlData = $this->getValuesFromXmlConfig($configContent);
        $composerData = $this->getValuesFromComposerConfig($composerContent);
        $this->validateConfigurationData($xmlData, $composerData);

        return array_merge($this->getArrayWithAllConfigKeysSetToNull(), $xmlData, $composerData);
    }

    /**
     * Check all required values are present and there is no mismatch should values be defined in both input sources
     *
     * @param array $xmlData
     * @param array $composerData
     * @return void
     */
    protected function validateConfigurationData(array $xmlData, array $composerData)
    {
        $this->checkForValueMismatches($xmlData, $composerData);
        $this->checkRequiredValuesArePresent($xmlData, $composerData);
    }
    
    /**
     * Return an array with all possible configuration keys initialized to null
     *
     * @return array
     */
    protected function getArrayWithAllConfigKeysSetToNull()
    {
        return [
            'version' => null,
            'title' => null,
            'media' => null,
            'parent' => null,
        ];
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
        return $parentTheme
            ? $this->formatComposerParentThemeParts($parentTheme)
            : null;
    }

    /**
     * Format the string parts of the parent theme array
     *
     * @param array $parentThemeParts
     * @return array
     */
    protected function formatComposerParentThemeParts(array $parentThemeParts)
    {
        return [ucfirst($parentThemeParts['vendor']), $parentThemeParts['name']];
    }

    /**
     * Parse theme name
     *
     * @param string $themePackageName
     * @return array|null Return array if theme name is in the right format, otherwise null is returned, for example:
     *   [
     *     'vendor' => 'magento',
     *     'name' => 'luma'
     *   ]
     */
    protected function parseComposerThemeName($themePackageName)
    {
        preg_match('/(?<vendor>.+)\/theme-(?<area>.+)-(?<name>.+)/', $themePackageName, $matches);
        return [
            'vendor' => $matches['vendor'],
            'name' => $matches['name'],
        ];
    }

    /**
     * Parse the configuration values from the XML theme configuration
     *
     * @param string $xmlContent
     * @return array
     */
    protected function getValuesFromXmlConfig($xmlContent)
    {
        $data = [];
        if (!empty($xmlContent)) {
            $dom = new \DOMDocument();
            $dom->loadXML($xmlContent);
            // todo: validation of the document
            $data = $this->extractValuesFromConfigDom($dom);
        }
        $this->validateTitleIsPresentInConfigFromXml($data);
        return $data;
    }

    /**
     * Extract the theme config values from the passed dom document node
     *
     * @param \DOMDocument $dom
     * @return array
     */
    protected function extractValuesFromConfigDom(\DOMDocument $dom)
    {
        return [
            'title' => $this->getTitleFromXmlConfig($dom),
            'version' => $this->getVersionFromXmlConfig($dom),
            'parent' => $this->getParentFromXmlConfig($dom),
            'media' => ['preview_image' => $this->getPreviewImageFromXmlConfig($dom)],
        ];
    }

    /**
     * Fetch the theme title from the given XML theme configuration
     *
     * @param \DOMDocument $dom
     * @return string
     */
    protected function getTitleFromXmlConfig(\DOMDocument $dom)
    {
        return $this->getNodeValueFromThemeXmlConfigIfExists($dom, 'title');
    }

    /**
     * Fetch the version title from the given XML theme configuration
     *
     * @param \DOMDocument $dom
     * @return string
     */
    protected function getVersionFromXmlConfig(\DOMDocument $dom)
    {
        return $this->getNodeValueFromThemeXmlConfigIfExists($dom, 'version');
    }

    /**
     * Fetch the theme parent from the given XML theme configuration
     *
     * @param \DOMDocument $dom
     * @return array|null
     */
    protected function getParentFromXmlConfig(\DOMDocument $dom)
    {
        $parent = $this->getNodeValueFromThemeXmlConfigIfExists($dom, 'parent');
        $pos = strpos($parent, '/');
        return $parent
            ? ['vendor' => substr($parent, 0, $pos), 'name' => substr($parent, $pos + 1)]
            : null;
    }

    /**
     * Fetch the theme preview image from the given XML theme configuration
     *
     * @param \DOMDocument $dom
     * @return string
     */
    protected function getPreviewImageFromXmlConfig(\DOMDocument $dom)
    {
        $themeNode = $dom->getElementsByTagName('theme')->item(0);
        $mediaNode = $themeNode->getElementsByTagName('media')->item(0);
        $previewImage = $mediaNode ? $mediaNode->getElementsByTagName('preview_image')->item(0)->nodeValue : '';
        return $previewImage;
    }

    /**
     * Fetch a child node value from the given XML theme configuration
     *
     * @param \DOMDocument $dom
     * @param string $nodeName
     * @return string
     */
    protected function getNodeValueFromThemeXmlConfigIfExists(\DOMDocument $dom, $nodeName)
    {
        /** @var $themeNode \DOMElement */
        $themeNode = $dom->getElementsByTagName('theme')->item(0);
        $targetNode = $themeNode->getElementsByTagName($nodeName)->item(0);
        return $targetNode ? $targetNode->nodeValue : null;
    }

    /**
     * Check the title is present in XML configuration because it is the only way to identify them theme further errors
     *
     * @param array $data
     * @return void
     */
    protected function validateTitleIsPresentInConfigFromXml(array $data)
    {
        if (!isset($data['title'])) {
            throw new \RuntimeException('Theme title configuration is missing');
        }
    }

    /**
     * Parse the configuration values from the composer JSON theme configuration
     *
     * @param string $composerContent
     * @return array
     */
    protected function getValuesFromComposerConfig($composerContent)
    {
        if (empty($composerContent)) {
            return [];
        }
        $json = json_decode($composerContent);
        $package = new Package($json);
        return $this->extractValuesFromComposerPackage($package);
    }

    /**
     * Compose the array with all relevant values from the composer package file
     *
     * @param Package $package
     * @return array
     */
    protected function extractValuesFromComposerPackage(Package $package)
    {
        return [
            'version' => $this->getVersionFromComposerConfig($package),
            'parent' => $this->getParentThemeFromComposerConfig($package)
        ];
    }

    /**
     * Return the theme version configuration from the composer package
     *
     * @param Package $package
     * @return string
     */
    protected function getVersionFromComposerConfig($package)
    {
        return $package->get('version');
    }

    /**
     * Return the parent theme configuration from the composer package if present
     *
     * @param Package $package
     * @return array|null
     */
    protected function getParentThemeFromComposerConfig(Package $package)
    {
        $parents = (array)$package->get('require', '/.+\/theme-/');
        $parents = empty($parents) ? null : array_keys($parents);
        return empty($parents) ? null : $this->parseComposerThemeName(array_shift($parents));
    }

    /**
     * Check if version and parent configuration values in XML and JSON match
     *
     * @param array $xmlData
     * @param array $composerData
     * @return void
     * @throws \UnexpectedValueException
     */
    protected function checkForValueMismatches(array $xmlData, array $composerData)
    {
        $this->checkForVersionMismatchIfDefinedInBoth($xmlData, $composerData);
        $this->checkForParentMismatchIfDefinedInBoth($xmlData, $composerData);
    }

    /**
     * If the version is defined in both the XML and the JSON config, check they are the same
     *
     * @param array $xmlData
     * @param array $composerData
     * @return void
     */
    protected function checkForVersionMismatchIfDefinedInBoth(array $xmlData, array $composerData)
    {
        if (isset($xmlData['version']) && isset($composerData['version'])) {
            $this->checkForVersionMismatch($xmlData, $composerData);
        }
    }

    /**
     * Check the version configuration in XML and JSON config matches
     *
     * @param array $xmlData
     * @param array $composerData
     * @return void
     */
    protected function checkForVersionMismatch(array $xmlData, array $composerData)
    {
        if ($xmlData['version'] != $composerData['version']) {
            $this->throwThemeVersionConfigMismatchException($xmlData['title']);
        }
    }

    /**
     * Throw exception with error message describing the theme config version mismatch
     *
     * @param string $themeTitle
     * @return void
     * @throws \UnexpectedValueException
     */
    protected function throwThemeVersionConfigMismatchException($themeTitle)
    {
        throw new \UnexpectedValueException(
            'The specified versions do not match ' .
            'between theme.xml and composer.json ' .
            'in the theme ' . $themeTitle
        );
    }

    /**
     * If the parent configuration is present in the XML and JSON config, check its the same
     *
     * @param array $xmlData
     * @param array $composerData
     * @return void
     */
    protected function checkForParentMismatchIfDefinedInBoth(array $xmlData, array $composerData)
    {
        if (isset($xmlData['parent']) && isset($composerData['parent'])) {
            $this->checkForParentMismatch($xmlData, $composerData);
        }
    }

    /**
     * Check the parent theme configuration in the XML and JSON config is the same
     *
     * @param array $xmlData
     * @param array $composerData
     * @return void
     */
    protected function checkForParentMismatch(array $xmlData, array $composerData)
    {
        $xmlParent = implode('/', $xmlData['parent']);
        $composerParent = implode('/', $this->formatComposerParentThemeParts($composerData['parent']));
        if ($xmlParent != $composerParent) {
            $this->throwParentThemeConfigMismatch($xmlData['title'], $xmlParent, $composerParent);
        }
    }

    /**
     * Throw exception with error message describing the theme config parent theme mismatch
     *
     * @param string $themeTitle
     * @param string $xmlParentName
     * @param string $composerParentName
     * @return void
     * @throws \UnexpectedValueException
     */
    protected function throwParentThemeConfigMismatch($themeTitle, $xmlParentName, $composerParentName)
    {
        throw new \UnexpectedValueException(
            'The specified parent themes do not match ' .
            'between theme.xml and composer.json ' .
            'in the theme ' . $themeTitle . ': ' .
            $xmlParentName . ' != ' . $composerParentName
        );
    }

    /**
     * @param array $xmlData
     * @param array $composerData
     * @return void
     */
    protected function checkRequiredValuesArePresent(array $xmlData, array $composerData)
    {
        if (!isset($xmlData['version']) && !isset($composerData['version'])) {
            throw new \RuntimeException('Version configuration is missing from theme');
        }
    }
}
