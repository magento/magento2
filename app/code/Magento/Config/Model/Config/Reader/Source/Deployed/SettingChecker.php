<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Reader\Source\Deployed;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Model\Config\Reader;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config;

/**
 * Class for checking settings that defined in config file
 */
class SettingChecker
{
    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param Config $config
     */
    public function __construct(
        ScopeCodeResolver $scopeCodeResolver,
        Config $config
    ) {
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->config = $config;
    }

    /**
     * Check that setting defined in deployed configuration
     *
     * @param string $path
     * @param string $scope
     * @param string|null $scopeCode
     * @return boolean
     */
    public function isReadOnly($path, $scope, $scopeCode = null)
    {
        $scopeCode = $this->scopeCodeResolver->resolve($scope, $scopeCode);
        $config = $this->config->get(System::CONFIG_TYPE);
        return $scope == ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            ? isset($config[ScopeConfigInterface::SCOPE_TYPE_DEFAULT][$path])
            : isset($config[$scope][$scopeCode][$path]);
    }
}
