<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
