<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Controller;

use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Setup\Controller\ConfigureCatalogSearch;
use Magento\Setup\Model\SearchConfigOptionsList;
use PHPUnit\Framework\TestCase;

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

    protected function setup(): void
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
            'engine' => 'elasticsearch7',
            'elasticsearch' => [
                'hostname' => 'localhost',
                'port' => '9200',
                'timeout' => '15',
                'indexPrefix' => 'magento2',
                'enableAuth' => false
            ]
        ];
        $this->assertEquals($expectedDefaultParameters, $jsonModel->getVariables());
    }
}
