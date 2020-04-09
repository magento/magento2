<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Magento\Setup\Controller\ConfigureCatalogSearch;
use Magento\Setup\Model\SearchConfigOptionsList;
use PHPUnit\Framework\TestCase;
use Zend\View\Model\ViewModel;

class ConfigureCatalogSearchTest extends TestCase
{
    /**
     * @var ConfigureCatalogSearch
     */
    private $controller;

    /**
     * @var SearchConfigOptionsList
     */
    private $searchConfigOptionsList;

    protected function setup()
    {
        $this->searchConfigOptionsList = new SearchConfigOptionsList();
        $this->controller = new ConfigureCatalogSearch($this->searchConfigOptionsList);
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertNotEmpty($viewModel->getVariables());
        $expectedAvailableSearchEngines = [
            'elasticsearch5' => 'Elasticsearch 5.x (deprecated)',
            'elasticsearch6' => 'Elasticsearch 6.x',
            'elasticsearch7' => 'Elasticsearch 7.x',
        ];
        $this->assertEquals($expectedAvailableSearchEngines, $viewModel->getVariable('availableSearchEngines'));
    }

    public function testDefaultParametersAction()
    {
        $jsonModel = $this->controller->defaultParametersAction();
        $this->assertInstanceOf(JsonModel::class, $jsonModel);

        $expectedDefaultParameters = [
            'engine' => SearchConfigOptionsList::DEFAULT_SEARCH_ENGINE,
            'elasticsearch' => [
                'hostname' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_HOST,
                'port' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_PORT,
                'timeout' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_TIMEOUT,
                'indexPrefix' => SearchConfigOptionsList::DEFAULT_ELASTICSEARCH_INDEX_PREFIX,
                'enableAuth' => false
            ]
        ];
        $this->assertEquals($expectedDefaultParameters, $jsonModel->getVariables());
    }
}
