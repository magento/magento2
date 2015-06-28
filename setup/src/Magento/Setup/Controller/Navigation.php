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
     * @param NavModel $navigation
     * @param Status $status
     */
    public function __construct(NavModel $navigation, Status $status)
    {
        $this->navigation = $navigation;
        $this->status = $status;
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
        $view = new ViewModel;
        if ($this->navigation->getType() === NavModel::NAV_INSTALLER) {
            $view->setTemplate('/magento/setup/navigation-installer/menu.phtml');
        } else {
            if ($this->status->isUpdateError() || $this->status->isUpdateInProgress()) {

            }
            $view->setTemplate('/magento/setup/navigation-updater/menu.phtml');
        }

        $view->setTerminal(true);
        $view->setVariable('menu', $this->navigation->getMenuItems());
        $view->setVariable('main', $this->navigation->getMainItems());
        return $view;
    }
}
