<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\ModuleStatus;
use Zend\Json\Json;

class Modules extends AbstractActionController
{
    /**
     * @var ModuleStatus
     */
    protected $allModules;

    /**
     * @param ModuleStatus $allModules
     */
    public function __construct(ModuleStatus $allModules)
    {
        $this->allModules = $allModules;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $some_list = ['modules' => $this->allModules->getAllModules()];
        $view = new ViewModel($some_list);
        $view->setTerminal(true);
        return $view;
    }
}
