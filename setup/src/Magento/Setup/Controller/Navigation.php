<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Backend\Model\UrlInterface;
use Magento\Setup\Exception;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\Navigation as NavModel;
use Magento\Setup\Model\ObjectManagerProvider;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 *  Controller Navigation class
 */
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
     * @var JsonModel
     */
    protected $json;

    /**
     * @var ObjectManagerProvider $objectManagerProvider
     */
    protected $objectManagerProvider;

    /**
     * @param NavModel $navigation
     * @param Status $status
     * @param ViewModel $viewModel
     * @param JsonModel $jsonModel
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        NavModel $navigation,
        Status $status,
        ViewModel $viewModel,
        JsonModel $jsonModel,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->navigation = $navigation;
        $this->status = $status;
        $this->objectManagerProvider = $objectManagerProvider->get();
        $this->view = $viewModel;
        $this->json = $jsonModel;
        $this->view->setVariable('menu', $this->navigation->getMenuItems());
        $this->view->setVariable('main', $this->navigation->getMainItems());
    }

    /**
     * Index Action
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        $this->json->setVariable('nav', $this->navigation->getData());
        $this->json->setVariable('menu', $this->navigation->getMenuItems());
        $this->json->setVariable('main', $this->navigation->getMainItems());
        $this->json->setVariable('titles', $this->navigation->getTitles());
        return $this->json;
    }

    /**
     * Menu Action
     *
     * @return array|ViewModel
     */
    public function menuAction()
    {
        $this->view->setVariable('menu', $this->navigation->getMenuItems());
        $this->view->setVariable('main', $this->navigation->getMainItems());
        $this->view->setTemplate('/magento/setup/navigation/menu.phtml');
        $this->view->setTerminal(true);
        return $this->view;
    }

    /**
     * SideMenu Action
     *
     * @return array|ViewModel
     *
     * @throws Exception
     */
    public function sideMenuAction()
    {
        /** @var UrlInterface $backendUrl */
        $backendUrl = $this->objectManagerProvider->get(UrlInterface::class);

        $this->view->setTemplate('/magento/setup/navigation/side-menu.phtml');
        $this->view->setVariable('isInstaller', $this->navigation->getType() ==  NavModel::NAV_INSTALLER);
        $this->view->setVariable('backendUrl', $backendUrl->getRouteUrl('adminhtml'));
        $this->view->setTerminal(true);
        return $this->view;
    }

    /**
     * HeaderBar Action
     *
     * @return array|ViewModel
     */
    public function headerBarAction()
    {
        if ($this->navigation->getType() === NavModel::NAV_UPDATER) {
            if ($this->status->isUpdateError() || $this->status->isUpdateInProgress()) {
                $this->view->setVariable('redirect', '../' . Environment::UPDATER_DIR . '/index.php');
            }
        }
        $this->view->setTemplate('/magento/setup/navigation/header-bar.phtml');
        $this->view->setTerminal(true);
        return $this->view;
    }
}
