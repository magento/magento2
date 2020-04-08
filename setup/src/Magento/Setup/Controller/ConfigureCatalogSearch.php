<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\SearchConfigOptionsList;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;

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
    public function indexAction()
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
    public function defaultParametersAction()
    {
        $defaults = [
            'engine' => SearchConfigOptionsList::DEFAULT_SEARCH_ENGINE,
            'elasticsearch' => [
                'hostname' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_HOST,
                'port' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_PORT,
                'timeout' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_TIMEOUT,
                'indexPrefix' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_INDEX_PREFIX,
                'enableAuth' => false,
                'username' => null,
                'password' => null
            ]
        ];

        return new JsonModel($defaults);
    }

    public function saveAction()
    {

    }
}
