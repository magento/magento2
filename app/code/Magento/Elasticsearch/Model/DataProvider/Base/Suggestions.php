<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\DataProvider\Base;

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Psr\Log\LoggerInterface;

/**
 * Default implementation to provide suggestions mechanism for Elasticsearch
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Suggestions implements SuggestedQueriesInterface
{
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
     * @var FieldProviderInterface
     */
    private $fieldProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetSuggestionFrequencyInterface
     */
    private $getSuggestionFrequency;

    /**
     * Suggestions constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param QueryResultFactory $queryResultFactory
     * @param ConnectionManager $connectionManager
     * @param SearchIndexNameResolver $searchIndexNameResolver
     * @param StoreManager $storeManager
     * @param FieldProviderInterface $fieldProvider
     * @param LoggerInterface|null $logger
     * @param GetSuggestionFrequencyInterface|null $getSuggestionFrequency
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config,
        QueryResultFactory $queryResultFactory,
        ConnectionManager $connectionManager,
        SearchIndexNameResolver $searchIndexNameResolver,
        StoreManager $storeManager,
        FieldProviderInterface $fieldProvider,
        LoggerInterface $logger = null,
        ?GetSuggestionFrequencyInterface $getSuggestionFrequency = null
    ) {
        $this->queryResultFactory = $queryResultFactory;
        $this->connectionManager = $connectionManager;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->searchIndexNameResolver = $searchIndexNameResolver;
        $this->storeManager = $storeManager;
        $this->fieldProvider = $fieldProvider;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->getSuggestionFrequency = $getSuggestionFrequency ?:
            ObjectManager::getInstance()->get(GetSuggestionFrequencyInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getItems(QueryInterface $query)
    {
        $result = [];
        if ($this->isSuggestionsAllowed()) {
            $isResultsCountEnabled = $this->isResultsCountEnabled();
            try {
                $suggestions = $this->getSuggestions($query);
            } catch (BadRequest400Exception $e) {
                $this->logger->critical($e);
                $suggestions = [];
            }

            foreach ($suggestions as $suggestion) {
                $count = null;
                if ($isResultsCountEnabled) {
                    try {
                        $count = $this->getSuggestionFrequency->execute($suggestion['text']);
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }

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
     * @inheritdoc
     */
    public function isResultsCountEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            SuggestedQueriesInterface::SEARCH_SUGGESTION_COUNT_RESULTS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Suggestions
     *
     * @param QueryInterface $query
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSuggestions(QueryInterface $query)
    {
        $suggestions = [];
        $searchSuggestionsCount = $this->getSearchSuggestionsCount();

        $searchQuery = $this->initQuery($query);
        $searchQuery = $this->addSuggestFields($searchQuery, $searchSuggestionsCount);

        $result = $this->fetchQuery($searchQuery);

        if (is_array($result)) {
            foreach ($result['suggest'] ?? [] as $suggest) {
                foreach ($suggest as $token) {
                    foreach ($token['options'] ?? [] as $key => $suggestion) {
                        $suggestions[$suggestion['score'] . '_' . $key] = $suggestion;
                    }
                }
            }
            ksort($suggestions);
            $texts = array_unique(array_column($suggestions, 'text'));
            $suggestions = array_slice(
                array_intersect_key(array_values($suggestions), $texts),
                0,
                $searchSuggestionsCount
            );
        }

        return $suggestions;
    }

    /**
     * Init Search Query
     *
     * @param string $query
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function initQuery($query)
    {
        $searchQuery = [
            'index' => $this->searchIndexNameResolver->getIndexName(
                $this->storeManager->getStore()->getId(),
                Config::ELASTICSEARCH_TYPE_DEFAULT
            ),
            'type' => Config::ELASTICSEARCH_TYPE_DEFAULT,
            'body' => [
                'suggest' => [
                    'text' => $query->getQueryText()
                ]
            ],
        ];

        return $searchQuery;
    }

    /**
     * Build Suggest on searchable fields.
     *
     * @param array $searchQuery
     * @param int $searchSuggestionsCount
     *
     * @return array
     */
    private function addSuggestFields($searchQuery, $searchSuggestionsCount)
    {
        $fields = $this->getSuggestFields();
        foreach ($fields as $field) {
            $searchQuery['body']['suggest']['phrase_' . $field] = [
                'phrase' => [
                    'field' => $field,
                    'analyzer' => 'standard',
                    'size' => $searchSuggestionsCount,
                    'max_errors' => 1,
                    'direct_generator' => [
                        [
                            'field' => $field,
                            'min_word_length' => 3,
                        ]
                    ],
                ],
            ];
        }

        return $searchQuery;
    }

    /**
     * Get fields to build suggest query on.
     *
     * @return array
     */
    private function getSuggestFields()
    {
        $fields = array_filter($this->fieldProvider->getFields(), function ($field) {
            return (($field['type'] ?? null) === 'text') && (($field['index'] ?? null) !== false);
        });

        return array_keys($fields);
    }

    /**
     * Fetch Query
     *
     * @param array $query
     * @return array
     */
    private function fetchQuery(array $query)
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
        return (int) $this->scopeConfig->getValue(
            SuggestedQueriesInterface::SEARCH_SUGGESTION_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is Search Suggestions Allowed
     *
     * @return bool
     */
    private function isSuggestionsAllowed()
    {
        $isSuggestionsEnabled = $this->scopeConfig->isSetFlag(
            SuggestedQueriesInterface::SEARCH_SUGGESTION_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        $isEnabled = $this->config->isElasticsearchEnabled();
        $isSuggestionsAllowed = ($isEnabled && $isSuggestionsEnabled);

        return $isSuggestionsAllowed;
    }
}
