<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model;

use Magento\Search\Model\QueryInterface;

interface SuggestedQueriesInterface
{
    /**
     * @param QueryInterface $query
     * @return \Magento\Search\Model\QueryResult[]
     */
    public function getItems(QueryInterface $query);

    /**
     * @return bool
     */
    public function isResultsCountEnabled();
}
