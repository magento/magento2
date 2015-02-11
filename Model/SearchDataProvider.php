<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model;

use Magento\Search\Model\QueryInterface;

class SearchDataProvider implements SearchDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSearchData(QueryInterface $query, $limit = null, $additionalFilters = [])
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isCountResultsEnabled()
    {
        return false;
    }
}
