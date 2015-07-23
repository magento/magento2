<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\SampleData;

class Success extends AbstractActionController
{
    /**
     * @var SampleData
     */
    protected $sampleData;

    /**
     * @param SampleData $sampleData
     */
    public function __construct(SampleData $sampleData)
    {
        $this->sampleData = $sampleData;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel([
            'isSampleDataErrorInstallation' => $this->sampleData->isInstallationError()
        ]);
        $view->setTerminal(true);
        return $view;
    }
}
