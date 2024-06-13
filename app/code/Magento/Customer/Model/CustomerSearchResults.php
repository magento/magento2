<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Customer search results.
 */
class CustomerSearchResults extends SearchResults implements CustomerSearchResultsInterface
{
}
