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
namespace Magento\Core\Model\Asset;

/**
 * View asset configuration interface
 */
class Config implements \Magento\Framework\View\Asset\ConfigInterface
{
    /**
     * XML path for CSS files merge configuration
     */
    const XML_PATH_MERGE_CSS_FILES = 'dev/css/merge_css_files';

    /**
     * XML path for JavaScript files merge configuration
     */
    const XML_PATH_MERGE_JS_FILES = 'dev/js/merge_files';

    /**
     * XML path for asset minification configuration
     */
    const XML_PATH_MINIFICATION_ENABLED = 'dev/%s/minify_files';

    /**
     * XML path for asset minification adapter configuration
     */
    const XML_PATH_MINIFICATION_ADAPTER = 'dev/%s/minify_adapter';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check whether merging of CSS files is on
     *
     * @return bool
     */
    public function isMergeCssFiles()
    {
        return (bool)$this->scopeConfig->isSetFlag(self::XML_PATH_MERGE_CSS_FILES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check whether merging of JavScript files is on
     *
     * @return bool
     */
    public function isMergeJsFiles()
    {
        return (bool)$this->scopeConfig->isSetFlag(self::XML_PATH_MERGE_JS_FILES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check whether asset minification is on for specified content type
     *
     * @param string $contentType
     * @return bool
     */
    public function isAssetMinification($contentType)
    {
        return (bool)$this->scopeConfig->isSetFlag(
            sprintf(self::XML_PATH_MINIFICATION_ENABLED, $contentType),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get asset minification adapter for specified content type
     *
     * @param string $contentType
     * @return string
     */
    public function getAssetMinificationAdapter($contentType)
    {
        return (string)$this->scopeConfig->getValue(
            sprintf(self::XML_PATH_MINIFICATION_ADAPTER, $contentType),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
