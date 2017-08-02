<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\Navigation as NavModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\Cron\Status;

/**
 * Class Navigation
 *
 * @since 2.0.0
 */
class Navigation extends AbstractActionController
{
    /**
     * @var NavModel
     * @since 2.0.0
     */
    protected $navigation;

    /**
     * @var Status
     * @since 2.0.0
     */
    protected $status;

    /**
     * @var ViewModel
     * @since 2.0.0
     */
    protected $view;

    /**
     * @param NavModel $navigation
     * @param Status $status
     * @since 2.0.0
     */
    public function __construct(NavModel $navigation, Status $status)
    {
        $this->navigation = $navigation;
        $this->status = $status;
        $this->view = new ViewModel;
        $this->view->setVariable('menu', $this->navigation->getMenuItems());
        $this->view->setVariable('main', $this->navigation->getMainItems());
    }

    /**
     * @return JsonModel
     * @since 2.0.0
     */
    public function indexAction()
    {
        $json = new JsonModel;
        $json->setVariable('nav', $this->navigation->getData());
        $json->setVariable('menu', $this->navigation->getMenuItems());
        $json->setVariable('main', $this->navigation->getMainItems());
        $json->setVariable('titles', $this->navigation->getTitles());
        return $json;
    }

    /**
     * @return array|ViewModel
     * @since 2.0.0
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
     * @return array|ViewModel
     * @since 2.0.0
     */
    public function sideMenuAction()
    {
        $this->view->setTemplate('/magento/setup/navigation/side-menu.phtml');
        $this->view->setVariable('isInstaller', $this->navigation->getType() ==  NavModel::NAV_INSTALLER);
        $this->view->setTerminal(true);
        return $this->view;
    }

    /**
     * @return array|ViewModel
     * @since 2.0.0
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
