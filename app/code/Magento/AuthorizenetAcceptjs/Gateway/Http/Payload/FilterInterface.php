<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Http\Payload;

/**
 * Describes a filter for filtering content after all the builders have finished
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
interface FilterInterface
{
    /**
     * Filters some data before use
     *
     * @param array $data
     * @return array
     */
    public function filter(array $data): array;
}
