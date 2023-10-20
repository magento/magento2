<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 */
class Config implements ScopeConfigInterface
{
    /**
     * Config cache tag
     */
    public const CACHE_TAG = 'CONFIG';

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @var ConfigTypeInterface[]
     */
    private $types;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var array
     */
    private $websiteCode = [];

    /**
     * Config constructor.
     *
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param array $types
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        ScopeCodeResolver $scopeCodeResolver,
        array $types = [],
        DeploymentConfig $deploymentConfig = null
    ) {
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->types = $types;
        $this->deploymentConfig = $deploymentConfig
            ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
    }

    /**
     * Retrieve config value by path and scope
     *
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
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }
        $configPath = $scope;
        if ($scope !== 'default') {
            if (is_numeric($scopeCode) || $scopeCode === null) {
                $scopeCode = $this->scopeCodeResolver->resolve($scope, $scopeCode);
            } elseif ($scopeCode instanceof \Magento\Framework\App\ScopeInterface) {
                $scopeCode = $scopeCode->getCode();
            }

            list($configPath, $scopeCode) = $this->checkDeploymentConfig(
                $scope,
                $configPath,
                $scopeCode,
                $path
            );

            if ($scopeCode) {
                $configPath .= '/' . $scopeCode;
            }
        }
        if ($path) {
            $configPath .= '/' . $path;
        }
        return $this->get('system', $configPath);
    }

    /**
     * Retrieve config flag
     *
     * @param string $path
     * @param string $scope
     * @param null|int|string $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return !!$this->getValue($path, $scope, $scopeCode);
    }

    /**
     * Invalidate cache by type
     *
     * Clean scopeCodeResolver
     *
     * @return void
     */
    public function clean()
    {
        foreach ($this->types as $type) {
            $type->clean();
        }
        $this->scopeCodeResolver->clean();
    }

    /**
     * Retrieve configuration.
     *
     * ('modules') - modules status configuration data
     * ('scopes', 'websites/base') - base website data
     * ('scopes', 'stores/default') - default store data
     *
     * ('system', 'default/web/seo/use_rewrites') - default system configuration data
     * ('system', 'websites/base/web/seo/use_rewrites') - 'base' website system configuration data
     *
     * ('i18n', 'default/en_US') - translations for default store and 'en_US' locale
     *
     * @param string $configType
     * @param string|null $path
     * @param mixed|null $default
     * @return array
     */
    public function get($configType, $path = '', $default = null)
    {
        $result = null;
        if (isset($this->types[$configType])) {
            $result = $this->types[$configType]->get($path);
        }

        return $result !== null ? $result : $default;
    }

    /**
     * Disable show internals with var_dump
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }

    /**
     * Check deployment config and update scope config
     *
     * @param string $scope
     * @param string $configPath
     * @param string $scopeCode
     * @param string $path
     * @return array
     */
    private function checkDeploymentConfig($scope, $configPath, $scopeCode, $path)
    {
        $defaultValue = $this->deploymentConfig->get(
            'system/' . ScopeConfigInterface::SCOPE_TYPE_DEFAULT . '/' . $path
        );
        if ($scope === 'stores') {
            $websiteCode = $this->getWebsiteCodeFromStore($scope, $scopeCode);
            if ($websiteCode) {
                $websiteValue = $this->deploymentConfig->get('system/websites/' . $websiteCode . '/' . $path);
                if ($websiteValue != null) {
                    $configPath = 'websites';
                    $scopeCode = $websiteCode;
                } elseif ($defaultValue != null) {
                    $configPath = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                    $scopeCode = null;
                }
            }
        } elseif ($defaultValue != null) {
            $configPath = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeCode = null;
        }
        return [$configPath, $scopeCode];
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
}
