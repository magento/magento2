<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Http\Payload;

/**
 * Describes a filter for filtering content after all the builders have finished
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
