<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Recommendations;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;

/**
 * Class \Magento\AdvancedSearch\Model\Recommendations\DataProvider
 *
 * @since 2.0.0
 */
class DataProvider implements SuggestedQueriesInterface
{
    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_ENABLED
     */
    const CONFIG_IS_ENABLED = 'catalog/search/search_recommendations_enabled';

    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_COUNT_RESULTS_ENABLED
     */
    const CONFIG_RESULTS_COUNT_ENABLED = 'catalog/search/search_recommendations_count_results_enabled';

    /**
     * @deprecated
     * @see SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_COUNT
     */
    const CONFIG_RESULTS_COUNT = 'catalog/search/search_recommendations_count';

    /**
     * @var \Magento\Search\Model\QueryResultFactory
     * @since 2.0.0
     */
    private $queryResultFactory;

    /**
     * @var \Magento\Catalog\Model\Layer
     * @since 2.0.0
     */
    protected $searchLayer;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    private $scopeConfig;

    /**
     * @var \Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory
     * @since 2.0.0
     */
    private $recommendationsFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory $recommendationsFactory
     * @param \Magento\Search\Model\QueryResultFactory $queryResultFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function getSearchRecommendationsCount()
    {
        return (int)$this->scopeConfig->getValue(
            self::CONFIG_RESULTS_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
