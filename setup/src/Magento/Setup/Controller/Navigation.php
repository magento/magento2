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

/**
 * Class Navigation builds the navigation view
 */
class Navigation extends AbstractActionController
{
    /**
     * @var NavModel
     */
    protected $navigation;

    /**
     * @var ViewModel
     */
    protected $view;

    /**
     * @param NavModel $navigation
     */
    public function __construct(NavModel $navigation)
    {
        $this->navigation = $navigation;
        $this->view = new ViewModel;
        $this->view->setVariable('menu', $this->navigation->getMenuItems());
        $this->view->setVariable('main', $this->navigation->getMainItems());
    }

    /**
     * Set values for the index action
     *
     * @return JsonModel
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
     * Set values for the view action
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
}
