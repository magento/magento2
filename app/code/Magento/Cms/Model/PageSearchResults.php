<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\PageSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Page search results.
 */
class PageSearchResults extends SearchResults implements PageSearchResultsInterface
{
}
