<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
