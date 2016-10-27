<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Reader\Source\Deployed;

use Magento\Config\Model\Config\Reader;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;

/**
 * Class for checking settings that defined in config file
 */
class SettingChecker
{
    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * @param DeploymentConfig $config
     */
    public function __construct(
        DeploymentConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * Resolve path by scope and scope code
     *
     * @param string $scope
     * @param string $scopeCode
     * @return string
     */
    private function resolvePath($scope, $scopeCode)
    {
        $scopePath = 'system/' . $scope;

        if ($scope != ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $scopePath .= '/' . $this->getScopeCodeResolver()->resolve($scope, $scopeCode);
        }

        return $scopePath;
    }

    /**
     * Check that setting defined in deployed configuration
     *
     * @param string $path
     * @param string $scope
     * @return boolean
     */
    public function isReadOnly($path, $scope, $scopeCode)
    {
        $config = $this->config->get($this->resolvePath($scope, $scopeCode) . "/" . $path);
        return $config !== null;
    }

    /**
     * @return ScopeCodeResolver
     */
    private function getScopeCodeResolver()
    {
        return ObjectManager::getInstance()->get(ScopeCodeResolver::class);
    }
}
