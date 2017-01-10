<?php
/**
 * Backend area front name resolver. Reads front name from configuration
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

    const XML_PATH_USE_CUSTOM_ADMIN_URL = 'admin/url/use_custom';

    const XML_PATH_CUSTOM_ADMIN_URL = 'admin/url/custom';

    /**
     * Backend area code
     */
    const AREA_CODE = 'adminhtml';

    /**
     * @var array
     */
    protected $standardPorts = ['http' => '80', 'https' => '443'];

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
    private $scopeConfig;

    /**
     * @param \Magento\Backend\App\Config $config
     * @param DeploymentConfig $deploymentConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Backend\App\Config $config,
        DeploymentConfig $deploymentConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->defaultFrontName = $deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME);
        $this->scopeConfig = $scopeConfig;
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
     *
     * @return bool
     */
    public function isHostBackend()
    {
        if ($this->scopeConfig->getValue(self::XML_PATH_USE_CUSTOM_ADMIN_URL, ScopeInterface::SCOPE_STORE)) {
            $backendUrl = $this->scopeConfig->getValue(self::XML_PATH_CUSTOM_ADMIN_URL, ScopeInterface::SCOPE_STORE);
        } else {
            $backendUrl = $this->scopeConfig->getValue(Store::XML_PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE);
        }
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        return stripos($this->getHostWithPort($backendUrl), $host) !== false;
    }

    /**
     * Get host with port
     *
     * @param string $url
     * @return mixed|string
     */
    private function getHostWithPort($url)
    {
        $scheme = parse_url(trim($url), PHP_URL_SCHEME);
        $host = parse_url(trim($url), PHP_URL_HOST);
        $port = parse_url(trim($url), PHP_URL_PORT);
        if (!$port) {
            $port = isset($this->standardPorts[$scheme]) ? $this->standardPorts[$scheme] : null;
        }
        return isset($port) ? $host . ':' . $port : $host;
    }
}
