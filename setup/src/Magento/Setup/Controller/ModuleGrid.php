<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\Grid;

/**
 * Controller for module grid tasks
 * @since 2.2.0
 */
class ModuleGrid extends \Zend\Mvc\Controller\AbstractActionController
{
    /**
     * Module grid
     *
     * @var Grid\Module
     * @since 2.2.0
     */
    private $gridModule;

    /**
     * @param Grid\Module $gridModule
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
