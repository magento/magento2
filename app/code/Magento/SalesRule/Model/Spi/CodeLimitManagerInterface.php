<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Spi;

use Magento\SalesRule\Api\Exception\CodeRequestLimitException;

/**
 * Determine whether number of requests for coupon codes has reached a limit.
 */
interface CodeLimitManagerInterface
{
    /**
     * Checks whether the request for a code was issued after reaching a limit.
     *
     * @param string $code
     * @throws CodeRequestLimitException If a limit has been reached.
     * @return void
     */
    public function checkRequest(string $code): void;
}
