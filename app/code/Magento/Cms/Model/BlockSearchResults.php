<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\BlockSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Block search results.
 */
class BlockSearchResults extends SearchResults implements BlockSearchResultsInterface
{
}
