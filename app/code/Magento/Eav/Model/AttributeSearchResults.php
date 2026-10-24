<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model;

use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Eav Attribute search results.
 */
class AttributeSearchResults extends SearchResults implements AttributeSearchResultsInterface
{
}
