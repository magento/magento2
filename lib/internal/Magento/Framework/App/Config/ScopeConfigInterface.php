<?php
/**
 * Configuration interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Config;

/**
 * @api
 * @since 100.0.2
 */
interface ScopeConfigInterface
{
    /**
     * Default scope type
     */
    const SCOPE_TYPE_DEFAULT = 'default';

    /**
     * Retrieve config value by path and scope.
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|int|string|\Magento\Store\Api\Data\StoreInterface $scopeCode
     * @return mixed
     */
    public function getValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);

    /**
     * Retrieve config flag by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|int|string|\Magento\Store\Api\Data\StoreInterface $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);
}
