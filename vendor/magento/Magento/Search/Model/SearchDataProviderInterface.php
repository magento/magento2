<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Search\Model;

interface SearchDataProviderInterface
{
    /**
     * @param QueryInterface $query
     * @param int $limit
     * @param array $additionalFilters
     * @return \Magento\Search\Model\QueryResult[]
     */
    public function getSearchData(QueryInterface $query, $limit = null, $additionalFilters = []);

    /**
     * @return bool
     */
    public function isCountResultsEnabled();
}
