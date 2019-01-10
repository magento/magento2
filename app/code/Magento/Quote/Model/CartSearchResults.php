<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Quote\Api\Data\CartSearchResultsInterface;

class CartSearchResults extends SearchResults implements CartSearchResultsInterface
{
}
