<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\DataProvider;

use Magento\Store\Model\ScopeInterface;
use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;

class Suggestions implements SuggestedQueriesInterface
{
    const CONFIG_SUGGESTION_COUNT = 'catalog/search/search_suggestion_count';
    const CONFIG_SUGGESTION_COUNT_RESULTS_ENABLED = 'catalog/search/search_suggestion_count_results_enabled';
    const CONFIG_SUGGESTION_ENABLED = 'catalog/search/search_suggestion_enabled';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Search\Model\QueryResultFactory
     */
    private $queryResultFactory;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var \Magento\Solr\Model\QueryFactory
     */
    private $queryFactory;

    /**
     * @var \Magento\Solr\SearchAdapter\AccessPointMapperInterface
     */
    private $accessPointMapper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Search\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Magento\Framework\Search\SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param \Magento\Search\Model\QueryResultFactory $queryResultFactory
     * @param ConnectionManager $connectionManager
     * @param \Magento\Framework\Search\Request\Builder $requestBuilder
     * @param \Magento\Framework\Search\SearchEngineInterface $searchEngine
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Config $config,
        \Magento\Search\Model\QueryResultFactory $queryResultFactory,
        ConnectionManager $connectionManager,
        \Magento\Framework\Search\Request\Builder $requestBuilder,
        \Magento\Framework\Search\SearchEngineInterface $searchEngine
    ) {
        // @TODO
        $this->queryResultFactory = $queryResultFactory;
        $this->connectionManager = $connectionManager;
        $this->queryResultFactory = $queryResultFactory;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->requestBuilder = $requestBuilder;
        $this->searchEngine = $searchEngine;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getItems(QueryInterface $query, $limit = null, $additionalFilters = null)
    {
        $result = [];
        if ($this->isSuggestionsAllowed()) {
            $isResultsCountEnabled = $this->isResultsCountEnabled();

            foreach ($this->getSuggestions($query) as $suggestion) {
                $count = null;
                if ($isResultsCountEnabled) {
                    $count = isset($suggestion['freq']) ? $suggestion['freq'] : null;
                }
                $result[] = $this->queryResultFactory->create(
                    [
                        'queryText' => $suggestion['text'],
                        'resultsCount' => $count,
                    ]
                );
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isResultsCountEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_SUGGESTION_COUNT_RESULTS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param QueryInterface $query
     * @return array
     */
    private function getSuggestions(QueryInterface $query)
    {
        $suggestions = [];
        $searchSuggestionsCount = $this->getSearchSuggestionsCount();

        $suggestRequest = [
            'index' => $this->config->getIndexName(),
            'body' => [
                'suggestions' => [
                    'text' => $query->getQueryText(),
                    'phrase' => [
                        'field' => '_all',
                        'analyzer' => 'standard',
                        'size' => $searchSuggestionsCount,
                        'max_errors' => 2,
                        'collate' => [
                            'query' => [
                                'bool' => [
                                    'must' => [
                                        'match' => [
                                            '{{field_name}}' => '{{suggestion}}'
                                        ],
                                    ],
                                ],
                            ],
                            'params' => [ 'field_name' => '_all' ],
                            'prune' => true
                        ],
                        'direct_generator' => [
                            [
                                'field' => '_all',
                                'min_word_length' => 3,
                                'min_doc_freq' => 1
                            ]
                        ],
                    ]
                ]
            ]
        ];

        $result = $this->fetchQuery($suggestRequest);

        if (is_array($result)) {
            foreach ($result['suggestions'] as $token) {
                foreach ($token['options'] as $key => $suggestion) {
                    $suggestions[$suggestion['score'] . '_' . $key] = $suggestion;
                }
            }
            ksort($suggestions);
            $suggestions = array_slice($suggestions, 0, $searchSuggestionsCount);
        }

        return $suggestions;
    }

    /**
     * @param array $query
     * @return array
     */
    private function fetchQuery(array $query)
    {
        return $this->connectionManager->getConnection()->suggest($query);
    }

    /**
     * Get search suggestions Max Count from config
     *
     * @return int
     */
    private function getSearchSuggestionsCount()
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_SUGGESTION_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    private function isSuggestionsAllowed()
    {
        $isSearchSuggestionsEnabled = (bool)$this->scopeConfig->getValue(
            self::CONFIG_SUGGESTION_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        $isEnabled = $this->config->isElasticsearchEnabled();
        $isSuggestionsAllowed = ($isEnabled && $isSearchSuggestionsEnabled);
        return $isSuggestionsAllowed;
    }
}
