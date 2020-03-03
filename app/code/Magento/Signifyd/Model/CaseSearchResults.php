<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Signifyd\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Signifyd\Api\Data\CaseSearchResultsInterface;

/**
 * Service Data Object with Case entities search results.
 */
class CaseSearchResults extends SearchResults implements CaseSearchResultsInterface
{
}
