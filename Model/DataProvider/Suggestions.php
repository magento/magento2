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

            $result[] = $this->queryResultFactory->create(
                [
                    'queryText' => 'test',
                    'resultsCount' => 5,
                ]
            );
            return $result;

            // @TODO
            foreach ($this->getSuggestions($query) as $suggestion) {
                /** @var Suggestion $suggestion */
                $count = null;
                if ($isResultsCountEnabled) {
                    $this->requestBuilder->setRequestName('quick_search_container');
                    $this->requestBuilder->bind('search_term', $suggestion->getWord());
                    $request = $this->requestBuilder->create();
                    /** @var \Magento\Framework\Search\ResponseInterface|\Countable $searchResult */
                    $searchResult = $this->searchEngine->search($request);
                    $count = $searchResult->count();
                }
                $result[] = $this->queryResultFactory->create(
                    [
                        'queryText' => $suggestion->getWord(),
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
     * @return Suggestion[]|Result
     */
    private function getSuggestions(QueryInterface $query)
    {
        // @TODO
        $request = $this->createQuery();
        $request->setHandler($this->accessPointMapper->getHandler());
        $request->setRows(0);
        $spellcheck = $request->getSpellcheck();
        $spellcheck->setQuery($query->getQueryText());
        $spellcheck->setCount($this->getSearchSuggestionsCount());
        /** @var \Solarium\QueryType\Select\Result\Result $resultSet */
        $resultSet = $this->fetchQuery($request);
        $suggestions = $resultSet->getSpellcheck() ?: [];
        return $suggestions;
    }

    /**
     * @return Query
     */
    private function createQuery()
    {
        // @TODO
        return $this->queryFactory->create();
    }

    /**
     * @param Query $query
     * @return array
     */
    private function fetchQuery(Query $query)
    {
        return $this->connectionManager->getConnection()->query($query);
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
