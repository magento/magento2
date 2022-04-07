<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Api\Data\ImsTokenSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with ims token search results.
 */
class ImsTokenSearchResults extends SearchResults implements ImsTokenSearchResultsInterface
{
}
