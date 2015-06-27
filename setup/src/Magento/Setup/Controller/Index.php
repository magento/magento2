<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Main controller of the Setup Wizard
 */
class Index extends AbstractActionController
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->objectManager = $objectManagerProvider->get();
    }

    /**
     * @return ViewModel|\Zend\Http\Response
     */
    public function indexAction()
    {
        if ($this->objectManager->get('Magento\Framework\App\DeploymentConfig')->isAvailable()) {
            /** @var \Magento\Framework\App\State $adminAppState */
            $adminAppState = $this->objectManager->get('Magento\Framework\App\State');
            $adminAppState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMIN);

            $this->objectManager->create(
                'Magento\Backend\Model\Auth\Session',
                [
                    'sessionConfig' => $this->objectManager->get('Magento\Backend\Model\Session\AdminConfig'),
                    'appState' => $adminAppState
                ]
            );
            if (!$this->objectManager->get('Magento\Backend\Model\Auth')->isLoggedIn()) {
                $view = new ViewModel();
                $view->setTemplate('/error/401.phtml');
                $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_401);
                return $view;
            }
        }
        return new ViewModel();
    }
}
