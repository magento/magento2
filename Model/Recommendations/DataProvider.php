<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Recommendations;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;

class DataProvider implements SuggestedQueriesInterface
{
    const CONFIG_IS_ENABLED = 'catalog/search/search_recommendations_enabled';
    const CONFIG_RESULTS_COUNT_ENABLED = 'catalog/search/search_recommendations_count_results_enabled';
    const CONFIG_RESULTS_COUNT = 'catalog/search/search_recommendations_count';

    /**
     * @var \Magento\Search\Model\QueryResultFactory
     */
    private $queryResultFactory;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $searchLayer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory
     */
    private $recommendationsFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory $recommendationsFactory
     * @param \Magento\Search\Model\QueryResultFactory $queryResultFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory $recommendationsFactory,
        \Magento\Search\Model\QueryResultFactory $queryResultFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->searchLayer = $layerResolver->get();
        $this->recommendationsFactory = $recommendationsFactory;
        $this->queryResultFactory = $queryResultFactory;
    }

    /**
     * @return bool
     */
    public function isResultsCountEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_RESULTS_COUNT_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(QueryInterface $query)
    {
        $recommendations = [];

        if (!$this->isSearchRecommendationsEnabled()) {
            return [];
        }

        foreach ($this->getSearchRecommendations($query) as $recommendation) {
            $recommendations[] = $this->queryResultFactory->create(
                [
                    'queryText' => $recommendation['query_text'],
                    'resultsCount' => $recommendation['num_results'],
                ]
            );
        }
        return $recommendations;
    }

    /**
     * @param QueryInterface $query
     * @return array
     */
    private function getSearchRecommendations(\Magento\Search\Model\QueryInterface $query)
    {
        $recommendations = [];

        if ($this->isSearchRecommendationsEnabled()) {
            $productCollection = $this->searchLayer->getProductCollection();
            $params = ['store_id' => $productCollection->getStoreId()];

            /** @var \Magento\AdvancedSearch\Model\ResourceModel\Recommendations $recommendationsResource */
            $recommendationsResource = $this->recommendationsFactory->create();
            $recommendations = $recommendationsResource->getRecommendationsByQuery(
                $query->getQueryText(),
                $params,
                $this->getSearchRecommendationsCount()
            );
        }

        return $recommendations;
    }

    /**
     * @return bool
     */
    private function isSearchRecommendationsEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    private function getSearchRecommendationsCount()
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_RESULTS_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
