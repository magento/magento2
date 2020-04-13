<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Setup\Model\SearchConfigOptionsList;

/**
 * ConfigureCatalogSearch controller
 */
class ConfigureCatalogSearch extends AbstractActionController
{
    /**
     * @var SearchConfigOptionsList
     */
    private $searchConfigOptionsList;

    /**
     * Default values to prefill form
     *
     * @var array
     */
    private $prefillConfigValues = [
        'engine' => 'elasticsearch7',
        'elasticsearch' => [
            'hostname' => 'localhost',
            'port' => '9200',
            'timeout' => '15',
            'indexPrefix' => 'magento2',
            'enableAuth' => false
        ]
    ];

    /**
     * @param SearchConfigOptionsList $searchConfigOptionsList
     */
    public function __construct(SearchConfigOptionsList $searchConfigOptionsList)
    {
        $this->searchConfigOptionsList = $searchConfigOptionsList;
    }

    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction(): ViewModel
    {
        $view = new ViewModel([
            'availableSearchEngines' => $this->searchConfigOptionsList->getAvailableSearchEngineList(),
        ]);
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Fetch default configuration parameters
     *
     * @return JsonModel
     */
    public function defaultParametersAction(): JsonModel
    {
        return new JsonModel($this->prefillConfigValues);
    }
}
