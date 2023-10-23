<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Plugin\App;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ScopeInterface;

/**
 * Plugin that for change config scope when have deployment config value
 */
class CheckConfigScope
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
     * @var array
     */
    protected $websiteCode = [];

    /**
     * Config constructor.
     *
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ScopeCodeResolver $scopeCodeResolver,
        DeploymentConfig $deploymentConfig
    ) {
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Before plugin for change scope config base deployment config
     *
     * @param Config $subject
     * @param string $path
     * @param string $scope
     * @param string $scopeCode
     * @return array
     */
    public function beforeGetValue(
        Config $subject,
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }

        $defaultValue = $this->deploymentConfig->get(
            'system/' . ScopeConfigInterface::SCOPE_TYPE_DEFAULT . '/' . $path
        );
        if ($scope === 'stores') {
            $websiteCode = $this->getWebsiteCodeFromStore($scope, $this->getScopeCode($scope, $scopeCode));
            if ($websiteCode) {
                $websiteValue = $this->deploymentConfig->get('system/websites/' . $websiteCode . '/' . $path);
                if ($websiteValue != null) {
                    $scope = 'websites';
                    $scopeCode = $websiteCode;
                } elseif ($defaultValue != null) {
                    $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                    $scopeCode = null;
                }
            }
        } elseif ($defaultValue != null) {
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeCode = null;
        }

        return [
            $path,
            $scope,
            $scopeCode
        ];
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
     * @param string $scopeCode
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
