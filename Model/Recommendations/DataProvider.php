<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Recommendations;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Store\ScopeInterface;
use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SearchDataProviderInterface;

class DataProvider implements SearchDataProviderInterface
{
    const CONFIG_SEARCH_RECOMMENDATIONS_ENABLED = 'catalog/search/search_recommendations_enabled';
    const CONFIG_SEARCH_RECOMMENDATIONS_COUNT_RESULTS_ENABLED = 'catalog/search/search_recommendations_count_results_enabled';
    const CONFIG_SEARCH_RECOMMENDATIONS_RESULTS_COUNT = 'catalog/search/search_recommendations_count';

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
     * @var \Magento\Search\Model\QueryFactory
     */
    private $queryFactory;

    /**
     * @var \Magento\AdvancedSearch\Model\Resource\RecommendationsFactory
     */
    private $recommendationsFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Magento\AdvancedSearch\Model\Resource\RecommendationsFactory $recommendationsFactory
     * @param \Magento\Search\Model\QueryResultFactory $queryResultFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\AdvancedSearch\Model\Resource\RecommendationsFactory $recommendationsFactory,
        \Magento\Search\Model\QueryResultFactory $queryResultFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->searchLayer = $layerResolver->get();
        $this->queryFactory = $queryFactory;
        $this->recommendationsFactory = $recommendationsFactory;
        $this->queryResultFactory = $queryResultFactory;
    }

    /**
     * @return bool
     */
    public function isCountResultsEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_SEARCH_RECOMMENDATIONS_COUNT_RESULTS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter
     */
    public function getSearchData(QueryInterface $query, $limit = null, $additionalFilters = [])
    {
        $recommendations = [];

        if (!$this->isSearchRecommendationsEnabled()) {
            return [];
        }

        foreach ($this->getSearchRecommendations() as $recommendation) {
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
     * @return array
     */
    private function getSearchRecommendations()
    {
        $productCollection = $this->searchLayer->getProductCollection();
        $searchQueryText = $this->queryFactory->get()->getQueryText();

        $params = ['store_id' => $productCollection->getStoreId()];

        $searchRecommendationsEnabled = $this->isSearchRecommendationsEnabled();
        $searchRecommendationsCount = $this->getSearchRecommendationsCount();

        if ($searchRecommendationsCount < 1) {
            $searchRecommendationsCount = 1;
        }
        if ($searchRecommendationsEnabled) {
            /** @var \Magento\AdvancedSearch\Model\Resource\Recommendations $recommendations */
            $recommendations = $this->recommendationsFactory->create();
            return $recommendations->getRecommendationsByQuery(
                $searchQueryText,
                $params,
                $searchRecommendationsCount
            );
        } else {
            return [];
        }
    }

    /**
     * @return bool
     */
    private function isSearchRecommendationsEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIG_SEARCH_RECOMMENDATIONS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    private function getSearchRecommendationsCount()
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_SEARCH_RECOMMENDATIONS_RESULTS_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
