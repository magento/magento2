<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\ConfigInterface as ViewAssetConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * View asset configuration interface
 */
class Config implements ViewAssetConfigInterface
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
     * XML path for asset minification adapter configuration
     */
    const XML_PATH_JS_BUNDLING = 'dev/js/enable_js_bundling';

    /**
     * XML path for HTML minification configuration
     */
    const XML_PATH_MINIFICATION_HTML = 'dev/template/minify_html';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check whether merging of CSS files is on
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isMergeCssFiles($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_MERGE_CSS_FILES,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Check whether bundling of JavScript files is on
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isBundlingJsFiles($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_JS_BUNDLING,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Check whether merging of JavScript files is on
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isMergeJsFiles($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_MERGE_JS_FILES,
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Check whether minify of HTML is on
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isMinifyHtml($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_MINIFICATION_HTML,
            $scopeType,
            $scopeCode
        );
    }
}
