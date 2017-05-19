<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Mvc\Bootstrap;

class Authorization
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Backend\Model\Session\AdminConfig
     */
    private $sessionAdminConfig;

    /**
     * @var \Magento\Backend\Model\Auth\SessionFactory
     */
    private $authSessionFactory;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    /**
     * @var \Magento\Backend\Model\UrlFactory
     */
    private $urlFactory;

    /**
     * @var \Magento\Backend\App\BackendAppList
     */
    private $backendAppList;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Backend\Model\Session\AdminConfig $sessionAdminConfig
     * @param \Magento\Backend\Model\Auth\SessionFactory $authSessionFactory
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Model\UrlFactory $urlFactory
     * @param \Magento\Backend\App\BackendAppList $backendAppList
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Model\Session\AdminConfig $sessionAdminConfig,
        \Magento\Backend\Model\Auth\SessionFactory $authSessionFactory,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Model\UrlFactory $urlFactory,
        \Magento\Backend\App\BackendAppList $backendAppList
    ) {
        $this->appState = $appState;
        $this->sessionAdminConfig = $sessionAdminConfig;
        $this->authSessionFactory = $authSessionFactory;
        $this->auth = $auth;
        $this->urlFactory = $urlFactory;
        $this->backendAppList = $backendAppList;
    }

    /**
     * Check if user logged in and has permissions to access web setup wizard
     *
     * @return bool
     */
    public function authorize()
    {
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $cookiePath = $this->getCookiePath();
        $this->sessionAdminConfig->setCookiePath($cookiePath);
        $adminSession = $this->authSessionFactory->create(
            [
                'sessionConfig' => $this->sessionAdminConfig,
                'appState' => $this->appState
            ]
        );

        if (!$this->auth->isLoggedIn() || !$adminSession->isAllowed('Magento_Backend::setup_wizard')) {
            $adminSession->destroy();
            return false;
        }
        return true;
    }

    /**
     * Get cookie path
     *
     * @return string
     */
    private function getCookiePath()
    {
        $backendApp = $this->backendAppList->getBackendApp('setup');
        $baseUrl = parse_url($this->urlFactory->create()->getBaseUrl(), PHP_URL_PATH);
        $baseUrl = \Magento\Framework\App\Request\Http::getUrlNoScript($baseUrl);
        return $baseUrl . $backendApp->getCookiePath();
    }
}
