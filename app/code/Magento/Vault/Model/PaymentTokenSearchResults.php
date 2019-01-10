<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;

class PaymentTokenSearchResults extends SearchResults implements PaymentTokenSearchResultsInterface
{
}
