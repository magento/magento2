<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\Grid;

/**
 * Controller for module grid tasks
 */
class ModuleGrid extends \Zend\Mvc\Controller\AbstractActionController
{
    /**
     * Module grid
     *
     * @var Grid\Module
     */
    private $gridModule;

    /**
     * @param Grid\Module $gridModule
     */
    public function __construct(
        Grid\Module $gridModule
    ) {
        $this->gridModule = $gridModule;
    }

    /**
     * Index page action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view = new \Zend\View\Model\ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Get Components info action
     *
     * @return \Zend\View\Model\JsonModel
     * @throws \RuntimeException
     */
    public function modulesAction()
    {
        $moduleList = $this->gridModule->getList();

        return new \Zend\View\Model\JsonModel(
            [
                'success' => true,
                'modules' => $moduleList,
                'total' => count($moduleList),
            ]
        );
    }
}
