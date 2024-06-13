<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Framework\Api\SearchResults;
use Magento\SalesRule\Api\Data\RuleSearchResultInterface;

/**
 * Service Data Object with Sales Rule search results.
 *
 * @phpcs:ignoreFile
 */
class RuleSearchResult extends SearchResults implements RuleSearchResultInterface
{
    /**
     * @inheritdoc
     */
    public function setItems(?array $items = null)
    {
        return parent::setItems($items);
    }
}
