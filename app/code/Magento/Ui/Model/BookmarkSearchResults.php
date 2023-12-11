<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterface;

/**
 * Service Data Object with Bookmark search results.
 */
class BookmarkSearchResults extends SearchResults implements BookmarkSearchResultsInterface
{
}
