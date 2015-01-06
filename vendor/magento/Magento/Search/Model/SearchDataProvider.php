<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Search\Model;

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
