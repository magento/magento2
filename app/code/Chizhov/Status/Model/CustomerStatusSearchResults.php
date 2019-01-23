<?php

declare(strict_types=1);

namespace Chizhov\Status\Model;

use Magento\Framework\Api\SearchResults;
use Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterface;

class CustomerStatusSearchResults extends SearchResults implements CustomerStatusSearchResultsInterface
{
}
