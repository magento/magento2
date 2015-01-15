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

class Navigation extends AbstractActionController
{
    /**
     * @var NavModel
     */
    protected $navigation;

    /**
     * @param NavModel $navigation
     */
    public function __construct(NavModel $navigation)
    {
        $this->navigation = $navigation;
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
        $view->setTemplate('/magento/setup/navigation/menu.phtml');
        $view->setTerminal(true);
        $view->setVariable('menu', $this->navigation->getMenuItems());
        $view->setVariable('main', $this->navigation->getMainItems());
        return $view;
    }
}
