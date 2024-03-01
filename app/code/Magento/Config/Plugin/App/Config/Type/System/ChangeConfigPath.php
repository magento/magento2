<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Plugin\App\Config\Type\System;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ScopeInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * Plugin that for change config path when have deployment config value
 */
class ChangeConfigPath
{
    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * The resolver for configuration paths according to source type.
     *
     * @var ConfigPathResolver
     */
    private $configPathResolver;

    /**
     * @var array
     */
    private $websiteCode = [];

    /**
     * Config constructor.
     *
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param DeploymentConfig $deploymentConfig
     * @param ConfigPathResolver $configPathResolver
     */
    public function __construct(
        ScopeCodeResolver $scopeCodeResolver,
        DeploymentConfig $deploymentConfig,
        ConfigPathResolver $configPathResolver
    ) {
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->deploymentConfig = $deploymentConfig;
        $this->configPathResolver = $configPathResolver;
    }

    /**
     * Before plugin for change config path base deployment config
     *
     * @param System $subject
     * @param string $path
     * @return array
     */
    public function beforeGet(
        System $subject,
        $path = ''
    ) {
        $pathParts = explode('/', $path);
        $scopeType = array_shift($pathParts);
        $scopeId = array_shift($pathParts);
        if ($path === '' || $scopeType === ScopeInterface::SCOPE_DEFAULT) {
            return [$path];
        }

        $configPath = implode('/', $pathParts);

        if ($scopeType === StoreScopeInterface::SCOPE_STORES) {
            $websiteCode = $this->getWebsiteCodeFromStore($scopeType, $this->getScopeCode($scopeType, $scopeId));
            if ($websiteCode) {
                if ($this->isLocked($configPath, StoreScopeInterface::SCOPE_WEBSITES, $websiteCode)) {
                    array_unshift($pathParts, StoreScopeInterface::SCOPE_WEBSITES, $websiteCode);
                    $path = implode('/', $pathParts);
                } elseif ($this->isLocked($configPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)) {
                    array_unshift($pathParts, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
                    $path = implode('/', $pathParts);
                }
            }
        } elseif ($this->isLocked($configPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)) {
            array_unshift($pathParts, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
            $path = implode('/', $pathParts);
        }

        return [$path];
    }

    /**
     * Checks whether configuration is locked in file storage.
     *
     * @param string $path The path to configuration
     * @param string $scope The scope of configuration
     * @param string $scopeCode The scope code of configuration
     * @return bool
     */
    private function isLocked($path, $scope, $scopeCode)
    {
        $scopePath = $this->configPathResolver->resolve($path, $scope, $scopeCode, System::CONFIG_TYPE);

        return $this->deploymentConfig->get($scopePath) !== null;
    }

    /**
     * Get Website code from store code
     *
     * @param string $scope
     * @param string $scopeCode
     * @return false|mixed
     */
    private function getWebsiteCodeFromStore($scope, $scopeCode)
    {
        if (!array_key_exists($scopeCode, $this->websiteCode)) {
            try {
                $websiteCode = false;
                $resolverScope = $this->scopeCodeResolver->resolvedScopeCode($scope, $scopeCode);
                if ($resolverScope instanceof ScopeInterface) {
                    $websiteCode = $resolverScope->getWebsite()->getCode();
                }
            } catch (\Exception $e) {
                $websiteCode = false;
            }
            $this->websiteCode[$scopeCode] = $websiteCode;
        }
        return $this->websiteCode[$scopeCode];
    }

    /**
     * Get Scope code
     *
     * @param string $scope
     * @param null|int|string $scopeCode
     * @return string
     */
    private function getScopeCode($scope, $scopeCode)
    {
        if (is_numeric($scopeCode) || $scopeCode === null) {
            $scopeCode = $this->scopeCodeResolver->resolve($scope, $scopeCode);
        } elseif ($scopeCode instanceof ScopeInterface) {
            $scopeCode = $scopeCode->getCode();
        }
        return $scopeCode;
    }
}
