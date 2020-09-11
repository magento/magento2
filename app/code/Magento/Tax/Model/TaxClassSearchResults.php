<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Tax\Api\Data\TaxClassSearchResultsInterface;

/**
 * Service Data Object with Tax Class search results.
 */
class TaxClassSearchResults extends SearchResults implements TaxClassSearchResultsInterface
{
}
