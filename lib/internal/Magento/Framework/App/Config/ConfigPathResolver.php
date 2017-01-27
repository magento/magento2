<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Store\Model\ScopeInterface;

/**
 * Resolves config path by input parameters.
 */
class ConfigPathResolver
{
    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @param ScopeCodeResolver $scopeCodeResolver
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
     * @param string|int|null $scopeCode The scope code or identifier of configuration
     * @param string|null $type The type of configuration
     * @return string Resolved configuration path
     */
    public function resolve($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null, $type = null)
    {
        $path = trim($path, '/');
        $scope = rtrim($scope, 's');

        /**
         * Scope name is currently stored in plural form.
         * Desired behavior will be changed in mayor release.
         */
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

        return $scopePath . '/' . $path;
    }
}
