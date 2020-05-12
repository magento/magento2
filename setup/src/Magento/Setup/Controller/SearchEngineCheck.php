<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Controller;

use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Magento\Framework\Exception\InputException;
use Magento\Setup\Model\SearchConfigOptionsList;
use Magento\Setup\Validator\ElasticsearchConnectionValidator;

/**
 * SearchEngineCheck controller
 */
class SearchEngineCheck extends AbstractActionController
{
    /**
     * @var ElasticsearchConnectionValidator
     */
    private $connectionValidator;

    /**
     * @var SearchConfigOptionsList
     */
    private $searchConfigOptionsList;

    /**
     * @param ElasticsearchConnectionValidator $connectionValidator
     * @param SearchConfigOptionsList $searchConfigOptionsList
     */
    public function __construct(
        ElasticsearchConnectionValidator $connectionValidator,
        SearchConfigOptionsList $searchConfigOptionsList
    ) {
        $this->connectionValidator = $connectionValidator;
        $this->searchConfigOptionsList = $searchConfigOptionsList;
    }

    /**
     * Result of checking Elasticsearch connection
     *
     * @return JsonModel
     */
    public function indexAction(): JsonModel
    {
        try {
            $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
            $this->isValidSearchEngine($params);
            $isValid = $this->connectionValidator->isValidConnection(
                [
                    'hostname' => $params['elasticsearch']['hostname'] ?? null,
                    'port' => $params['elasticsearch']['port'] ?? null,
                    'enableAuth' => $params['elasticsearch']['enableAuth'] ?? false,
                    'username' => $params['elasticsearch']['username'] ?? null,
                    'password' => $params['elasticsearch']['password'] ?? null,
                    'indexPrefix' => $params['elasticsearch']['indexPrefix'] ?? ''
                ]
            );
            return new JsonModel(['success' => $isValid]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Check search engine parameter is valid
     *
     * @param array $requestParams
     * @return bool
     * @throws InputException
     */
    private function isValidSearchEngine(array $requestParams): bool
    {
        $selectedEngine = $requestParams['engine'] ?? null;
        $availableSearchEngines = $this->searchConfigOptionsList->getAvailableSearchEngineList();
        if (empty($selectedEngine) || !isset($availableSearchEngines[$selectedEngine])) {
            throw new InputException(__('Please select a valid search engine.'));
        }

        return true;
    }
}
