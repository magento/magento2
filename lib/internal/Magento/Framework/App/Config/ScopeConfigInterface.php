<?php
/**
 * Configuration interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Config;

/**
 * @api
 */
interface ScopeConfigInterface
{
    /**
     * Default scope type
     */
    const SCOPE_DEFAULT = 'default';

    /**
     * Retrieve config value by path and scope.
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scope The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|string $scopeCode
     * @return mixed
     */
    public function getValue($path, $scope = ScopeConfigInterface::SCOPE_DEFAULT, $scopeCode = null);

    /**
     * Retrieve config flag by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scope The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|string $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scope = ScopeConfigInterface::SCOPE_DEFAULT, $scopeCode = null);
}
