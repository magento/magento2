<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Request\Aggregation;

/**
 * Interface \Magento\Framework\Search\Request\Aggregation\StatusInterface
 *
 * @api
 */
interface StatusInterface
{
    /**
     * @return bool
     */
    public function isEnabled();
}
