<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdvancedSearch\Model;

use Magento\Search\Model\QueryInterface;

/**
 * @api
 * @since 100.0.2
 */
interface SuggestedQueriesInterface
{
    /**#@+
     * Recommendations settings config paths
     */
    public const SEARCH_RECOMMENDATIONS_ENABLED = 'catalog/search/search_recommendations_enabled';
    public const SEARCH_RECOMMENDATIONS_COUNT_RESULTS_ENABLED =
        'catalog/search/search_recommendations_count_results_enabled';
    public const SEARCH_RECOMMENDATIONS_COUNT = 'catalog/search/search_recommendations_count';
    /**#@-*/

    /**#@+
     * Suggestions settings config paths
     */
    public const SEARCH_SUGGESTION_COUNT = 'catalog/search/search_suggestion_count';
    public const SEARCH_SUGGESTION_COUNT_RESULTS_ENABLED = 'catalog/search/search_suggestion_count_results_enabled';
    public const SEARCH_SUGGESTION_ENABLED = 'catalog/search/search_suggestion_enabled';
    /**#@-*/

    /**
     * Retrieve search results
     *
     * @param QueryInterface $query
     * @return \Magento\Search\Model\QueryResult[]
     */
    public function getItems(QueryInterface $query);

    /**
     * Check for counting results
     *
     * @return bool
     */
    public function isResultsCountEnabled();
}
