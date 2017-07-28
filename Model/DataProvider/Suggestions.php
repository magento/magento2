<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\DataProvider;

use Magento\Search\Model\QueryInterface;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;

/**
 * Class \Magento\AdvancedSearch\Model\DataProvider\Suggestions
 *
 * @since 2.1.0
 */
class Suggestions implements SuggestedQueriesInterface
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function isResultsCountEnabled()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getItems(QueryInterface $query)
    {
        return [];
    }
}
