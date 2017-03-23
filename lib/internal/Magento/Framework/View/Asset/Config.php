<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;

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
     * @return bool
     */
    public function isMergeCssFiles()
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_MERGE_CSS_FILES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check whether bundling of JavScript files is on
     *
     * @return bool
     */
    public function isBundlingJsFiles()
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_JS_BUNDLING,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check whether merging of JavScript files is on
     *
     * @return bool
     */
    public function isMergeJsFiles()
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_MERGE_JS_FILES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check whether minify of HTML is on
     *
     * @return bool
     */
    public function isMinifyHtml()
    {
        return (bool)$this->scopeConfig->isSetFlag(
            self::XML_PATH_MINIFICATION_HTML,
            ScopeInterface::SCOPE_STORE
        );
    }
}
