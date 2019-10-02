<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model;

use Magento\Eav\Api\Data\AttributeGroupSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Attribute Group search results.
 */
class AttributeGroupSearchResults extends SearchResults implements AttributeGroupSearchResultsInterface
{
}
