<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\App\Area;

use Laminas\Uri\Uri;
use Magento\Backend\App\Config;
use Magento\Backend\Setup\ConfigOptionsList;
use Magento\Framework\App\Area\FrontNameResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Front name resolver for backend area.
 *
 * @api
 * @since 100.0.2
 */
class FrontNameResolver implements FrontNameResolverInterface
{
    public const XML_PATH_USE_CUSTOM_ADMIN_PATH = 'admin/url/use_custom_path';

    public const XML_PATH_CUSTOM_ADMIN_PATH = 'admin/url/custom_path';

    public const XML_PATH_USE_CUSTOM_ADMIN_URL = 'admin/url/use_custom';

    public const XML_PATH_CUSTOM_ADMIN_URL = 'admin/url/custom';

    /**
     * Backend area code
     */
    public const AREA_CODE = 'adminhtml';

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

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Config $config
     * @param DeploymentConfig $deploymentConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param Uri $uri
     * @param RequestInterface $request
     */
    public function __construct(
        Config $config,
        DeploymentConfig $deploymentConfig,
        ScopeConfigInterface $scopeConfig,
        ?Uri $uri = null,
        ?RequestInterface $request = null
    ) {
        $this->config = $config;
        $this->defaultFrontName = $deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME);
        $this->scopeConfig = $scopeConfig;
        $this->uri = $uri ?: ObjectManager::getInstance()->get(Uri::class);
        $this->request = $request ?: ObjectManager::getInstance()->get(RequestInterface::class);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return bool
     */
    public function isHostBackend()
    {
        if (!$this->request->getServer('HTTP_HOST')) {
            return false;
        }

        if ($this->scopeConfig->getValue(self::XML_PATH_USE_CUSTOM_ADMIN_URL, ScopeInterface::SCOPE_STORE)) {
            $backendUrl = $this->scopeConfig->getValue(self::XML_PATH_CUSTOM_ADMIN_URL, ScopeInterface::SCOPE_STORE);
        } else {
            $xmlPath = $this->request->isSecure() ? Store::XML_PATH_SECURE_BASE_URL : Store::XML_PATH_UNSECURE_BASE_URL;
            $backendUrl = $this->config->getValue($xmlPath);
            if ($backendUrl === null) {
                $backendUrl = $this->scopeConfig->getValue(
                    $xmlPath,
                    ScopeInterface::SCOPE_STORE
                );
            }
        }
        $this->uri->parse($backendUrl);
        $configuredHost = $this->uri->getHost();
        if (!$configuredHost) {
            return false;
        }

        $configuredPort = $this->uri->getPort() ?: ($this->standardPorts[$this->uri->getScheme()] ?? null);
        $uri = ($this->request->isSecure() ? 'https' : 'http') . '://' . $this->request->getServer('HTTP_HOST');
        $this->uri->parse($uri);
        $host = $this->uri->getHost();
        if ($configuredPort) {
            $configuredHost .= ':' . $configuredPort;
            $host .= ':' . ($this->uri->getPort() ?: $this->standardPorts[$this->uri->getScheme()]);
        }

        return strcasecmp($configuredHost, $host) === 0;
    }
}
