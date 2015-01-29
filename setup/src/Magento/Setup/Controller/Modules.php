<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\ModuleStatus;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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
        $view = new ViewModel([
            'modules' => $this->allModules->getAllModules(),
        ]);
        $view->setTemplate('/magento/setup/modules.phtml');
        $view->setTerminal(true);
        return $view;
    }
}
