<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\Lists;
use Magento\Setup\Model\ModuleStatus;
use Magento\Setup\Model\SampleData;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CustomizeYourStore extends AbstractActionController
{
    /**
     * @var Lists
     */
    protected $list;

    /**
     * @var SampleData
     */
    protected $sampleData;

    /**
     * @var ModuleStatus
     */
    protected $allModules;

    /**
     * @param Lists $list
     * @param SampleData $sampleData
     * @param ModuleStatus $allModules
     */
    public function __construct(Lists $list, SampleData $sampleData,ModuleStatus $allModules)
    {
        $this->list = $list;
        $this->sampleData = $sampleData;
        $this->allModules = $allModules;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel([
            'timezone' => $this->list->getTimezoneList(),
            'currency' => $this->list->getCurrencyList(),
            'language' => $this->list->getLocaleList(),
            'isSampledataEnabled' => $this->sampleData->isDeployed(),
            'modules' => $this->allModules->getAllModules(),
        ]);
        $view->setTerminal(true);
        return $view;
    }
}
