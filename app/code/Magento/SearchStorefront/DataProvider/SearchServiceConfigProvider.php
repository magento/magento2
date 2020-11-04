<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SearchStorefront\DataProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;

class SearchServiceConfigProvider implements ScopeConfigInterface
{
    const SCOPE_STORES = 'stores';
    const SCOPE_WEBSITES = 'websites';

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \Magento\SearchStorefront\Model\Scope\ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * DeploymentConfigProvider constructor.
     *
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\SearchStorefront\Model\Scope\ScopeCodeResolver $scopeCodeResolver,
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\SearchStorefront\Model\Scope\ScopeCodeResolver $scopeCodeResolver,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * Retrieve config value by path and scope
     * Config supports only fallback from scope STORES to DEFAULT and vise versa fallback
     * @param string $path
     * @param string $scope
     * @param null|int|string $scopeCode
     * @return mixed
     */
    public function getValue(
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($scope === 'store') {
            $scope = self::SCOPE_STORES;
        } elseif ($scope === 'website') {
            $scope = self::SCOPE_WEBSITES;
        }

        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            if (is_numeric($scopeCode) || $scopeCode === null) {
                $scopeCode = $this->scopeCodeResolver->resolve($scope, $scopeCode);
            } elseif ($scopeCode instanceof \Magento\Framework\App\ScopeInterface) {
                $scopeCode = $scopeCode->getCode();
            }

            $configPath = $scope;
        } else {
            $scopeCode = $this->scopeResolver->getScope()->getCode();
            $configPath = $scope = self::SCOPE_STORES;
        }

        if ($scopeCode) {
            $configPath .= '/' . $scopeCode;
        }

        $value = $this->get('system', $configPath . '/' . $path);

        if (!$value && $scope === self::SCOPE_STORES) {
            $value = $this->get('system', ScopeConfigInterface::SCOPE_TYPE_DEFAULT . '/' . $path);
        }

        return $value;
    }

    /**
     * @param string $path
     * @param string $scopeType
     * @param null $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return !!$this->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * @param string $mainScope
     * @param string $configPath
     * @return array|mixed|string|null
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function get(string $mainScope, string $configPath)
    {
        return $this->deploymentConfig->get($mainScope . '/' .$configPath, null);
    }
}
