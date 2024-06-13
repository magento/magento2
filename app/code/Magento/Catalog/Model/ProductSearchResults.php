<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Product search results.
 */
class ProductSearchResults extends SearchResults implements ProductSearchResultsInterface
{
}
