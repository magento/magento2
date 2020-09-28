<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Framework\ObjectManagerInterface;
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
     * @var ViewModel
     */
    protected $view;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManagerProvider;

    /**
     * @param NavModel              $navigation
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(NavModel $navigation, ObjectManagerProvider $objectManagerProvider)
    {
        $this->navigation = $navigation;
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
}
