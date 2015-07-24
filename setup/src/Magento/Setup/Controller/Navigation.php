<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\Navigation as NavModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\ObjectManagerProvider;

class Navigation extends AbstractActionController
{
    /**
     * @var NavModel
     */
    protected $navigation;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var ViewModel
     */
    protected $view;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param NavModel $navigation
     * @param Status $status
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(NavModel $navigation, Status $status, ObjectManagerProvider $objectManagerProvider)
    {
        $this->navigation = $navigation;
        $this->status = $status;
        $this->view = new ViewModel;
        $this->view->setVariable('menu', $this->navigation->getMenuItems());
        $this->view->setVariable('main', $this->navigation->getMainItems());
        $this->objectManager = $objectManagerProvider->get();
    }

    /**
     * @return JsonModel
     */
    public function indexAction()
    {
        $json = new JsonModel;
        return $json->setVariable('nav', $this->navigation->getData());
    }

    /**
     * @return array|ViewModel
     */
    public function menuAction()
    {
        $this->view->setTemplate('/magento/setup/navigation/menu.phtml');
        $this->view->setTerminal(true);
        return $this->view;
    }

    /**
     * @return array|ViewModel
     */
    public function sideMenuAction()
    {
        $this->view->setTemplate('/magento/setup/navigation/side-menu.phtml');
        $this->view->setVariable('isInstaller', $this->navigation->getType() ==  NavModel::NAV_INSTALLER);
        /** @var \Magento\Backend\Helper\Data $backendHelper*/
        $backendHelper = $this->objectManager->get('Magento\Backend\Helper\Data');
        $this->view->setVariable('homePageUrl', $backendHelper->getHomePageUrl());
        $this->view->setTerminal(true);
        return $this->view;
    }

    /**
     * @return array|ViewModel
     */
    public function headerBarAction()
    {
        if ($this->navigation->getType() === NavModel::NAV_INSTALLER) {
            $this->view->setVariable('headerTitle', 'Magento Installation');
        } else {
            if ($this->status->isUpdateError() || $this->status->isUpdateInProgress()) {
                $this->view->setVariable('redirect', '../' . Environment::UPDATER_DIR . '/index.php');
            }
            $this->view->setVariable('headerTitle', 'Magento Component Manager');
        }
        $this->view->setTemplate('/magento/setup/navigation/header-bar.phtml');
        $this->view->setTerminal(true);
        return $this->view;
    }
}
