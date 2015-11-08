<?php
/**
 * Backend area front name resolver. Reads front name from configuration
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Area;

use Magento\Backend\Setup\ConfigOptionsList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class FrontNameResolver implements \Magento\Framework\App\Area\FrontNameResolverInterface
{
    const XML_PATH_USE_CUSTOM_ADMIN_PATH = 'admin/url/use_custom_path';

    const XML_PATH_CUSTOM_ADMIN_PATH = 'admin/url/custom_path';

    /**
     * Backend area code
     */
    const AREA_CODE = 'adminhtml';

    /**
     * @var string
     */
    protected $defaultFrontName;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $config;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /** @var ScopeConfigInterface */
    private $configInterface;

    /**
     * @param \Magento\Backend\App\Config $config
     * @param DeploymentConfig $deploymentConfig
     * @param ScopeConfigInterface $configInterface
     */
    public function __construct(
        \Magento\Backend\App\Config $config,
        DeploymentConfig $deploymentConfig,
        ScopeConfigInterface $configInterface
    ) {
        $this->config = $config;
        $this->defaultFrontName = $deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME);
        $this->configInterface = $configInterface;
    }

    /**
     * Retrieve area front name
     *
     * @param bool $checkHost If true, verify front name is valid for this url (hostname is correct)
     * @return string|bool
     */
    public function getFrontName($checkHost = false)
    {
        if ($checkHost && !$this->isHostBackend()) {
            return false;
        }
        $isCustomPathUsed = (bool)(string)$this->config->getValue(self::XML_PATH_USE_CUSTOM_ADMIN_PATH);
        if ($isCustomPathUsed) {
            return (string)$this->config->getValue(self::XML_PATH_CUSTOM_ADMIN_PATH);
        }
        return $this->defaultFrontName;
    }

    /**
     * Return whether the host from request is the backend host
     * @return bool
     */
    public function isHostBackend()
    {
        $backendUrl = $this->configInterface->getValue(Store::XML_PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE);
        $backendHost = parse_url(trim($backendUrl), PHP_URL_HOST);
        $host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        return (strcasecmp($backendHost, $host) === 0);
    }
}
