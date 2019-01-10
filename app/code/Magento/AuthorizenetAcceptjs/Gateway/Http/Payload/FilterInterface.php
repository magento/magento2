<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Http\Payload;

/**
 * Describes a filter for use with the payload converter
 */
interface FilterInterface
{
    /**
     * Filters some data before use
     *
     * @param array $data
     * @return mixed
     */
    public function filter(array $data);
}
