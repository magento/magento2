<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Reader\Source\Deployed;

use Magento\Config\Model\Config\Reader;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\DeploymentConfig;

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
     * @var DeploymentConfig
     */
    private $config;

    /**
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param DeploymentConfig $config
     */
    public function __construct(
        ScopeCodeResolver $scopeCodeResolver,
        DeploymentConfig $config
    ) {
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->config = $config;
    }

    /**
     * Check that setting defined in deployed configuration
     *
     * @param string $path
     * @param string $scope
     * @return boolean
     */
    public function isReadOnly($path, $scope)
    {
        $config = $this->config->get('system/' . $scope . "/" . $path);
        return $config !== null;
    }
}
