<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model;

use Magento\Eav\Api\Data\AttributeSetSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Attribute Set search results.
 */
class AttributeSetSearchResults extends SearchResults implements AttributeSetSearchResultsInterface
{
}
