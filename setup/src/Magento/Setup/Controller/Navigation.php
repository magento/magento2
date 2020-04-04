<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\Navigation as NavModel;
use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Navigation controller
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
     * @var ObjectManagerInterface
     */
    private $objectManagerProvider;

    /**
     * @param NavModel              $navigation
     * @param Status                $status
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(NavModel $navigation, Status $status, ObjectManagerProvider $objectManagerProvider)
    {
        $this->navigation = $navigation;
        $this->status = $status;
        $this->objectManagerProvider = $objectManagerProvider->get();
        $this->view = new ViewModel();
        $this->view->setVariable('menu', $this->navigation->getMenuItems());
        $this->view->setVariable('main', $this->navigation->getMainItems());
    }

    /**
     * Index action
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        $json = new JsonModel();
        $json->setVariable('nav', $this->navigation->getData());
        $json->setVariable('menu', $this->navigation->getMenuItems());
        $json->setVariable('main', $this->navigation->getMainItems());
        $json->setVariable('titles', $this->navigation->getTitles());
        return $json;
    }

    /**
     * Menu action
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
     * Side menu action
     *
     * @return array|ViewModel
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
     * Head bar action
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
