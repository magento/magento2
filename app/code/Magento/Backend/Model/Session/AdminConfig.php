<?php
/**
 * Backend Session configuration object
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Session;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Filesystem;
use Magento\Framework\Session\Config;

/**
 * Magento Backend session configuration
 * @api
 */
class AdminConfig extends Config
{
    /**
     * Configuration for admin session name
     */
    const SESSION_NAME_ADMIN = 'admin';

    /**
     * @var FrontNameResolver
     */
    protected $_frontNameResolver;

    /**
     * @var \Magento\Backend\App\BackendAppList
     */
    private $backendAppList;

    /**
     * @var \Magento\Backend\Model\UrlFactory
     */
    private $backendUrlFactory;

    /**
     * @param \Magento\Framework\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Filesystem $filesystem
     * @param DeploymentConfig $deploymentConfig
     * @param string $scopeType
     * @param \Magento\Backend\App\BackendAppList $backendAppList
     * @param FrontNameResolver $frontNameResolver
     * @param \Magento\Backend\Model\UrlFactory $backendUrlFactory
     * @param string $lifetimePath
     * @param string $sessionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\ValidatorFactory $validatorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\StringUtils $stringHelper,
        \Magento\Framework\App\RequestInterface $request,
        Filesystem $filesystem,
        DeploymentConfig $deploymentConfig,
        $scopeType,
        \Magento\Backend\App\BackendAppList $backendAppList,
        FrontNameResolver $frontNameResolver,
        \Magento\Backend\Model\UrlFactory $backendUrlFactory,
        $lifetimePath = self::XML_PATH_COOKIE_LIFETIME,
        $sessionName = self::SESSION_NAME_ADMIN
    ) {
        parent::__construct(
            $validatorFactory,
            $scopeConfig,
            $stringHelper,
            $request,
            $filesystem,
            $deploymentConfig,
            $scopeType,
            $lifetimePath
        );
        $this->_frontNameResolver = $frontNameResolver;
        $this->backendAppList = $backendAppList;
        $this->backendUrlFactory = $backendUrlFactory;
        $adminPath = $this->extractAdminPath();
        $this->setCookiePath($adminPath);
        $this->setName($sessionName);
        $this->setCookieSecure($this->_httpRequest->isSecure());
    }

    /**
     * Determine the admin path
     *
     * @return string
     */
    private function extractAdminPath()
    {
        $backendApp = $this->backendAppList->getCurrentApp();
        $cookiePath = null;
        $baseUrl = parse_url($this->backendUrlFactory->create()->getBaseUrl(), PHP_URL_PATH);
        if (!$backendApp) {
            $cookiePath = $baseUrl . $this->_frontNameResolver->getFrontName();
            return $cookiePath;
        }
        //In case of application authenticating through the admin login, the script name should be removed
        //from the path, because application has own script.
        $baseUrl = \Magento\Framework\App\Request\Http::getUrlNoScript($baseUrl);
        $cookiePath = $baseUrl . $backendApp->getCookiePath();
        return $cookiePath;
    }

    /**
     * Set session cookie lifetime to session duration
     *
     * @return $this
     * @since 2.1.0
     */
    protected function configureCookieLifetime()
    {
        return $this->setCookieLifetime(0);
    }
}
