<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

/**
 * Sets up session for setup/index.php/session/prolong or redirects to error page
 */
class Session extends \Laminas\Mvc\Controller\AbstractActionController
{
    /**
     * @var \Laminas\ServiceManager\ServiceManager
     */
    private $serviceManager;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @param \Laminas\ServiceManager\ServiceManager $serviceManager
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        \Laminas\ServiceManager\ServiceManager $serviceManager,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
    ) {
        $this->serviceManager = $serviceManager;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * No index action, return 404 error page
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function indexAction()
    {
        $view = new \Laminas\View\Model\ViewModel();
        $view->setTemplate('/error/404.phtml');
        $this->getResponse()->setStatusCode(\Laminas\Http\Response::STATUS_CODE_404);
        return $view;
    }

    /**
     * Prolong session
     *
     * @return string
     */
    public function prolongAction()
    {
        try {
            if ($this->serviceManager->get(\Magento\Framework\App\DeploymentConfig::class)->isAvailable()) {
                $objectManager = $this->objectManagerProvider->get();
                /* @var \Magento\Backend\Model\Auth\Session $session */
                $session = $objectManager->get(\Magento\Backend\Model\Auth\Session::class);
                // check if session was already set in \Magento\Setup\Mvc\Bootstrap\InitParamListener::authPreDispatch
                if (!$session->isSessionExists()) {
                    /** @var \Magento\Framework\App\State $adminAppState */
                    $adminAppState = $objectManager->get(\Magento\Framework\App\State::class);
                    $adminAppState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
                    $sessionConfig = $objectManager->get(\Magento\Backend\Model\Session\AdminConfig::class);
                    /** @var \Magento\Backend\Model\Url $backendUrl */
                    $backendUrl = $objectManager->get(\Magento\Backend\Model\Url::class);
                    $urlPath = parse_url($backendUrl->getBaseUrl(), PHP_URL_PATH);
                    $cookiePath = $urlPath . 'setup';
                    $sessionConfig->setCookiePath($cookiePath);
                    /* @var \Magento\Backend\Model\Auth\Session $session */
                    $session = $objectManager->create(
                        \Magento\Backend\Model\Auth\Session::class,
                        [
                            'sessionConfig' => $sessionConfig,
                            'appState' => $adminAppState
                        ]
                    );
                }
                $session->prolong();
                return new \Laminas\View\Model\JsonModel(['success' => true]);
            }
        } catch (\Exception $e) {
        }
        return new \Laminas\View\Model\JsonModel(['success' => false]);
    }

    /**
     * Unlogin action, return 401 error page
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function unloginAction()
    {
        $view = new \Laminas\View\Model\ViewModel();
        $view->setTemplate('/error/401.phtml');
        $this->getResponse()->setStatusCode(\Laminas\Http\Response::STATUS_CODE_401);
        return $view;
    }
}
