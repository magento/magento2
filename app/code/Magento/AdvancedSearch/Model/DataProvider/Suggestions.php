<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\AdvancedSearch\Model\DataProvider;

use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;

class Suggestions implements SuggestedQueriesInterface
{
    /**
     * @inheritdoc
     */
    public function isResultsCountEnabled()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getItems(QueryInterface $query)
    {
        return [];
    }
}
