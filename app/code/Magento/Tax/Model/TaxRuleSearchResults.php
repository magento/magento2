<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Tax\Api\Data\TaxRuleSearchResultsInterface;

/**
 * Service Data Object with Tax Rule search results.
 */
class TaxRuleSearchResults extends SearchResults implements TaxRuleSearchResultsInterface
{
}
