<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block;

use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryResult;

class SuggestedQueriesMock implements SuggestedQueriesInterface
{
    /**
     * @var array
     */
    private $results = [];

    /**
     * @param QueryInterface $query
     * @return \Magento\Search\Model\QueryResult[]
     */
    public function getItems(QueryInterface $query)
    {
        $return = [];
        foreach ($this->results as $result) {
            $return[] = new QueryResult($result, 1);
        }
        return $return;
    }

    /**
     * @return bool
     */
    public function isResultsCountEnabled()
    {
        return true;
    }

    /**
     * @param array $results
     * @return void
     */
    public function setItems(array $results)
    {
        $this->results = $results;
    }
}
