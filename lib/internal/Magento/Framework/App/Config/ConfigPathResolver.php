<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Store\Model\ScopeInterface;

/**
 * Configures full path for configurations, including scope data and configuration type.
 * @since 2.2.0
 */
class ConfigPathResolver
{
    /**
     * @var ScopeCodeResolver
     * @since 2.2.0
     */
    private $scopeCodeResolver;

    /**
     * @param ScopeCodeResolver $scopeCodeResolver
     * @since 2.2.0
     */
    public function __construct(ScopeCodeResolver $scopeCodeResolver)
    {
        $this->scopeCodeResolver = $scopeCodeResolver;
    }

    /**
     * Creates full config path for given params.
     * If $type variable was provided, it will be used as first part of path.
     *
     * @param string $path The path of configuration
     * @param string $scope The scope of configuration
     * @param string|int|null $scopeCode The scope code or its identifier. The values for this
     * field are taken from 'store' or 'store_website' tables, depends on $scope value
     * @param string|null $type The type of configuration.
     * The available types are declared in implementations of Magento\Framework\App\Config\ConfigTypeInterface
     * E.g.
     * ```php
     * const CONFIG_TYPE = 'system';
     * ```
     * @return string Resolved configuration path
     * @since 2.2.0
     */
    public function resolve($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null, $type = null)
    {
        $path = trim($path, '/');
        $scope = rtrim($scope, 's');

        /** Scope name is currently stored in plural form. */
        if (in_array($scope, [ScopeInterface::SCOPE_STORE, ScopeInterface::SCOPE_WEBSITE])) {
            $scope .= 's';
        }

        $scopePath = $type ? $type . '/' . $scope : $scope;

        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            if (is_numeric($scopeCode) || $scopeCode === null) {
                $scopeCode = $this->scopeCodeResolver->resolve($scope, $scopeCode);
            }

            $scopePath .= '/' . $scopeCode;
        }

        return $scopePath . ($path ? '/' . $path : '');
    }
}
