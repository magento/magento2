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
        $defaults = [
            'engine' => SearchConfigOptionsList::DEFAULT_SEARCH_ENGINE,
            'elasticsearch' => [
                'hostname' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_HOST,
                'port' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_PORT,
                'timeout' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_TIMEOUT,
                'indexPrefix' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_INDEX_PREFIX,
                'enableAuth' => false
            ]
        ];

        return new JsonModel($defaults);
    }
}
