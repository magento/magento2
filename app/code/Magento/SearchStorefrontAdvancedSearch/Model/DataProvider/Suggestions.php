<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SearchStorefrontAdvancedSearch\Model\DataProvider;

use Magento\Search\Model\QueryInterface;
use Magento\SearchStorefrontAdvancedSearch\Model\SuggestedQueriesInterface;

class Suggestions implements SuggestedQueriesInterface
{
    /**
     * {@inheritdoc}
     */
    public function isResultsCountEnabled()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(QueryInterface $query)
    {
        return [];
    }
}
