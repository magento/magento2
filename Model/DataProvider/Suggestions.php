<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\DataProvider;

use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Suggestions implements SuggestedQueriesInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getItems(QueryInterface $query)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isResultsCountEnabled()
    {
        return false;
    }
}
