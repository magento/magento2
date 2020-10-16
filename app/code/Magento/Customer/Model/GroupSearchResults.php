<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Customer Groups search results.
 */
class GroupSearchResults extends SearchResults implements GroupSearchResultsInterface
{
}
