<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\DataProvider;

use Magento\Store\Model\ScopeInterface;
use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Search\Model\QueryResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

class Suggestions implements SuggestedQueriesInterface
{
    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_SUGGESTION_COUNT
     */
    const CONFIG_SUGGESTION_COUNT = 'catalog/search/search_suggestion_count';

    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_SUGGESTION_COUNT_RESULTS_ENABLED
     */
    const CONFIG_SUGGESTION_COUNT_RESULTS_ENABLED = 'catalog/search/search_suggestion_count_results_enabled';

    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_SUGGESTION_ENABLED
     */
    const CONFIG_SUGGESTION_ENABLED = 'catalog/search/search_suggestion_enabled';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var QueryResultFactory
     */
    private $queryResultFactory;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SearchIndexNameResolver
     */
    private $searchIndexNameResolver;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param QueryResultFactory $queryResultFactory
     * @param ConnectionManager $connectionManager
     * @param SearchIndexNameResolver $searchIndexNameResolver
     * @param StoreManager $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config,
        QueryResultFactory $queryResultFactory,
        ConnectionManager $connectionManager,
        SearchIndexNameResolver $searchIndexNameResolver,
        StoreManager $storeManager
    ) {
        $this->queryResultFactory = $queryResultFactory;
        $this->connectionManager = $connectionManager;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->searchIndexNameResolver = $searchIndexNameResolver;
        $this->storeManager = $storeManager;
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
            'index' => $this->searchIndexNameResolver->getIndexName(
                $this->storeManager->getStore()->getId(),
                Config::ELASTICSEARCH_TYPE_DEFAULT
            ),
            'body' => [
                'suggestions' => [
                    'text' => $query->getQueryText(),
                    'phrase' => [
                        'field' => '_all',
                        'analyzer' => 'standard',
                        'size' => $searchSuggestionsCount,
                        'max_errors' => 2,
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
