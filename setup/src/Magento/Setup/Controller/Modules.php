<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\ModuleStatus;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

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
     * @return JsonModel
     */
    public function indexAction()
    {
        return new JsonModel(['modules' => $this->allModules->getAllModules() ]);
    }
}
