<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\Module\ModuleList;
use Magento\Setup\Model\ObjectManagerProvider;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class Success extends AbstractActionController
{
    /**
     * @var ModuleList
     */
    protected $moduleList;

    /**
     * @var ObjectManagerProvider
     */
    protected $objectManagerProvider;

    /**
     * @param ModuleList $moduleList
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ModuleList $moduleList, ObjectManagerProvider $objectManagerProvider)
    {
        $this->moduleList = $moduleList;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        if ($this->moduleList->has('Magento_SampleData')) {
            /** @var \Magento\SampleData\Model\SampleData $sampleData */
            $sampleData = $this->objectManagerProvider->get()->get('Magento\SampleData\Model\SampleData');
            $isSampleDataErrorInstallation = $sampleData->isInstallationError();
        } else {
            $isSampleDataErrorInstallation = false;
        }
        $view = new ViewModel([
            'isSampleDataErrorInstallation' => $isSampleDataErrorInstallation
        ]);
        $view->setTerminal(true);
        return $view;
    }
}
