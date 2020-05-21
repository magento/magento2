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
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class CaseSearchResults extends SearchResults implements CaseSearchResultsInterface
{
}
