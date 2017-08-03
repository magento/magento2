<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\Module\ModuleList;
use Magento\Setup\Model\ObjectManagerProvider;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class \Magento\Setup\Controller\Success
 *
 * @since 2.0.0
 */
class Success extends AbstractActionController
{
    /**
     * @var ModuleList
     * @since 2.0.0
     */
    protected $moduleList;

    /**
     * @var ObjectManagerProvider
     * @since 2.0.0
     */
    protected $objectManagerProvider;

    /**
     * @param ModuleList $moduleList
     * @param ObjectManagerProvider $objectManagerProvider
     * @since 2.0.0
     */
    public function __construct(ModuleList $moduleList, ObjectManagerProvider $objectManagerProvider)
    {
        $this->moduleList = $moduleList;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * @return ViewModel
     * @since 2.0.0
     */
    public function indexAction()
    {
        if ($this->moduleList->has('Magento_SampleData')) {
            /** @var \Magento\Framework\Setup\SampleData\State $sampleData */
            $sampleData = $this->objectManagerProvider->get()->get(\Magento\Framework\Setup\SampleData\State::class);
            $isSampleDataErrorInstallation = $sampleData->hasError();
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
