<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with bulk Operation search result.
 */
class OperationSearchResults extends SearchResults implements OperationSearchResultsInterface
{
}
