<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model;

use Magento\Search\Model\QueryInterface;

class SuggestedQueries implements SuggestedQueriesInterface
{

    /**
     * {@inheritdoc}
     */
    public function isResultsCountEnabled()
    {
        return false;
    }

    /**
     * @param QueryInterface $query
     * @return \Magento\Search\Model\QueryResult[]
     */
    public function getItems(QueryInterface $query)
    {
        return [];
    }
}
