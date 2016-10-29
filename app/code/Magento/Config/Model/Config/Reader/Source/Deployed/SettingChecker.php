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
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @param DeploymentConfig $config
     * @param ScopeCodeResolver $scopeCodeResolver
     */
    public function __construct(
        DeploymentConfig $config,
        ScopeCodeResolver $scopeCodeResolver
    ) {
        $this->config = $config;
        $this->scopeCodeResolver = $scopeCodeResolver;
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
            $scopePath .= '/' . $this->scopeCodeResolver->resolve($scope, $scopeCode);
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
}
