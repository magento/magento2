<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model;

use Magento\Search\Model\QueryInterface;

/**
 * @api
 * @since 2.0.0
 */
interface SuggestedQueriesInterface
{
    /**#@+
     * Recommendations settings config paths
     */
    const SEARCH_RECOMMENDATIONS_ENABLED = 'catalog/search/search_recommendations_enabled';
    const SEARCH_RECOMMENDATIONS_COUNT_RESULTS_ENABLED = 'catalog/search/search_recommendations_count_results_enabled';
    const SEARCH_RECOMMENDATIONS_COUNT = 'catalog/search/search_recommendations_count';
    /**#@-*/

    /**#@+
     * Suggestions settings config paths
     */
    const SEARCH_SUGGESTION_COUNT = 'catalog/search/search_suggestion_count';
    const SEARCH_SUGGESTION_COUNT_RESULTS_ENABLED = 'catalog/search/search_suggestion_count_results_enabled';
    const SEARCH_SUGGESTION_ENABLED = 'catalog/search/search_suggestion_enabled';
    /**#@-*/

    /**
     * @param QueryInterface $query
     * @return \Magento\Search\Model\QueryResult[]
     * @since 2.0.0
     */
    public function getItems(QueryInterface $query);

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isResultsCountEnabled();
}
