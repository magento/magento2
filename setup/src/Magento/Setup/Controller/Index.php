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
     * @var \Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * @return ViewModel|\Zend\Http\Response
     */
    public function indexAction()
    {
        if (file_exists(BP . \Magento\Setup\Controller\Environment::PATH_TO_CONFIG)) {
            $objectManager = $this->objectManagerProvider->get();
            /** @var \Magento\Framework\App\State $adminAppState */
            $adminAppState = $objectManager->get('Magento\Framework\App\State');
            $adminAppState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMIN);

            $objectManager->create(
                'Magento\Backend\Model\Auth\Session',
                [
                    'sessionConfig' => $objectManager->get('Magento\Backend\Model\Session\AdminConfig'),
                    'appState' => $adminAppState
                ]
            );
            if (!$objectManager->get('Magento\Backend\Model\Auth')->isLoggedIn()) {
                $view = new ViewModel();
                $view->setTemplate('/error/401.phtml');
                $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_401);
                return $view;
            }
        }
        return new ViewModel();
    }
}
