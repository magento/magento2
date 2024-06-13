<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Tax\Api\Data\TaxRateSearchResultsInterface;

/**
 * Service Data Object with Tax Rate search results.
 */
class TaxRateSearchResults extends SearchResults implements TaxRateSearchResultsInterface
{
}
