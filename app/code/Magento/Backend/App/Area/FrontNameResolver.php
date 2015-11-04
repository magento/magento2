<?php
/**
 * Backend area front name resolver. Reads front name from configuration
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Area;

use Magento\Backend\Setup\ConfigOptionsList;
use Magento\Framework\App\DeploymentConfig;

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

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @param \Magento\Backend\App\Config $config
     * @param DeploymentConfig $deploymentConfig
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     */
    public function __construct(
        \Magento\Backend\App\Config $config,
        DeploymentConfig $deploymentConfig,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->config = $config;
        $this->defaultFrontName = $deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME);
        $this->backendUrl = $backendUrl;
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
        $backendHost = parse_url(trim($this->backendUrl->getBaseUrl()), PHP_URL_HOST);
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        return (strcasecmp($backendHost, $host) === 0);
    }
}
