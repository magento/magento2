<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

/**
 * Class \Magento\Setup\Controller\Session
 *
 * @since 2.0.0
 */
class Session extends \Zend\Mvc\Controller\AbstractActionController
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     * @since 2.0.0
     */
    private $serviceManager;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @since 2.0.0
     */
    private $objectManagerProvider;

    /**
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @since 2.0.0
     */
    public function __construct(
        \Zend\ServiceManager\ServiceManager $serviceManager,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
    ) {
        $this->serviceManager = $serviceManager;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * No index action, return 404 error page
     *
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     * @since 2.1.0
     */
    public function indexAction()
    {
        $view = new \Zend\View\Model\ViewModel();
        $view->setTemplate('/error/404.phtml');
        $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_404);
        return $view;
    }

    /**
     * Prolong session
     *
     * @return string
     * @since 2.0.0
     */
    public function prolongAction()
    {
        try {
            if ($this->serviceManager->get(\Magento\Framework\App\DeploymentConfig::class)->isAvailable()) {
                $objectManager = $this->objectManagerProvider->get();
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
                $session->prolong();
                return new \Zend\View\Model\JsonModel(['success' => true]);
            }
        } catch (\Exception $e) {
        }
        return new \Zend\View\Model\JsonModel(['success' => false]);
    }

    /**
     * @return \Zend\View\Model\ViewModel|\Zend\Http\Response
     * @since 2.0.0
     */
    public function unloginAction()
    {
        $view = new \Zend\View\Model\ViewModel();
        $view->setTemplate('/error/401.phtml');
        $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_401);
        return $view;
    }
}
