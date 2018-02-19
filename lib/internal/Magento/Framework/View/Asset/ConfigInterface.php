<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Store\Model\ScopeInterface;

/**
 * View asset configuration interface
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Check whether merging of CSS files is on
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isMergeCssFiles($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null);

    /**
     * Check whether merging of JavScript files is on
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isMergeJsFiles($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null);

    /**
     * Check whether bundling of JavScript files is on
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isBundlingJsFiles($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null);

    /**
     * Check whether minify of HTML is on
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isMinifyHtml($scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null);
}
