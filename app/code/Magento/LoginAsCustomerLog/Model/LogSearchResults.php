<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Model;

use Magento\Framework\Api\SearchResults;
use Magento\LoginAsCustomerLog\Api\Data\LogSearchResultsInterface;

/**
 * @inheritDoc
 */
class LogSearchResults extends SearchResults implements LogSearchResultsInterface
{
}
