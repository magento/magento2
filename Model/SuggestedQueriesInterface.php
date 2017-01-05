<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
