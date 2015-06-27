<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\Setup\Lists;
use Magento\Setup\Model\SampleData;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

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
     * @param Lists $list
     * @param SampleData $sampleData
     */
    public function __construct(Lists $list, SampleData $sampleData)
    {
        $this->list = $list;
        $this->sampleData = $sampleData;
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
            'isSampleDataInstalled' => $this->sampleData->isInstalledSuccessfully(),
            'isSampleDataErrorInstallation' => $this->sampleData->isInstallationError()
        ]);
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Getting default time zone from server settings
     *
     * @return JsonModel
     */
    public function defaultTimeZoneAction()
    {
        $defaultTimeZone = trim(@date_default_timezone_get());
        if (empty($defaultTimeZone)) {
            return new JsonModel(['defaultTimeZone' => 'UTC']);
        } else {
            return new JsonModel(['defaultTimeZone' => $defaultTimeZone]);
        }
    }
}
