<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Magento\Setup\Model\ObjectManagerProvider;
use Zend\View\Model\ViewModel;

class Session extends AbstractActionController
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->objectManager = $objectManagerProvider->get();
    }

    /**
     * Prolong session
     *
     * @return string
     */
    public function prolongAction()
    {
        try {
            if ($this->objectManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {
                /** @var \Magento\Framework\App\State $adminAppState */
                $adminAppState = $this->objectManager->get('Magento\Framework\App\State');
                $adminAppState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMIN);

                /* @var \Magento\Backend\Model\Auth\Session $session */
                $sessionConfig = $this->objectManager->get('Magento\Backend\Model\Session\AdminConfig');
                $sessionConfig->setCookiePath('/setup');
                $session = $this->objectManager->create(
                    'Magento\Backend\Model\Auth\Session',
                    [
                        'sessionConfig' => $sessionConfig,
                        'appState' => $adminAppState
                    ]
                );
                $session->prolong();
                return \Zend_Json::encode(['success' => true]);
            }
        } catch (\Exception $e) {
        }
        return \Zend_Json::encode(['success' => false]);
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
