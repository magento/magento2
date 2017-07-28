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

/**
 * Class \Magento\Elasticsearch\Model\DataProvider\Suggestions
 *
 * @since 2.1.0
 */
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
     * @since 2.1.0
     */
    private $config;

    /**
     * @var QueryResultFactory
     * @since 2.1.0
     */
    private $queryResultFactory;

    /**
     * @var ConnectionManager
     * @since 2.1.0
     */
    private $connectionManager;

    /**
     * @var ScopeConfigInterface
     * @since 2.1.0
     */
    private $scopeConfig;

    /**
     * @var SearchIndexNameResolver
     * @since 2.1.0
     */
    private $searchIndexNameResolver;

    /**
     * @var StoreManager
     * @since 2.1.0
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param QueryResultFactory $queryResultFactory
     * @param ConnectionManager $connectionManager
     * @param SearchIndexNameResolver $searchIndexNameResolver
     * @param StoreManager $storeManager
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    private function fetchQuery(array $query)
    {
        return $this->connectionManager->getConnection()->suggest($query);
    }

    /**
     * Get search suggestions Max Count from config
     *
     * @return int
     * @since 2.1.0
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
     * @since 2.1.0
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
