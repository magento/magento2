<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class Session extends AbstractActionController
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $serviceManager;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
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
     * @return ViewModel|\Zend\Http\Response
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTemplate('/error/404.phtml');
        $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_404);
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
            if ($this->serviceManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {
                $objectManager = $this->objectManagerProvider->get();
                /** @var \Magento\Framework\App\State $adminAppState */
                $adminAppState = $objectManager->get('Magento\Framework\App\State');
                $adminAppState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMIN);
                $sessionConfig = $objectManager->get('Magento\Backend\Model\Session\AdminConfig');
                /** @var \Magento\Backend\Model\Url $backendUrl */
                $backendUrl = $objectManager->get('Magento\Backend\Model\Url');
                $urlPath = parse_url($backendUrl->getBaseUrl(), PHP_URL_PATH);
                $cookiePath = $urlPath . 'setup';
                $sessionConfig->setCookiePath($cookiePath);
                /* @var \Magento\Backend\Model\Auth\Session $session */
                $session = $objectManager->create(
                    'Magento\Backend\Model\Auth\Session',
                    [
                        'sessionConfig' => $sessionConfig,
                        'appState' => $adminAppState
                    ]
                );
                $session->prolong();
                return new JsonModel(['success' => true]);
            }
        } catch (\Exception $e) {
        }
        return new JsonModel(['success' => false]);
    }

    /**
     * @return ViewModel|\Zend\Http\Response
     */
    public function unloginAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/error/401.phtml');
        $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_401);
        return $view;
    }
}
