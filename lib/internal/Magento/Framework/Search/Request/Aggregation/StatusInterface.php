<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

/**
 * Interface \Magento\Framework\Search\Request\Aggregation\StatusInterface
 *
 */
interface StatusInterface
{
    /**
     * @return bool
     */
    public function isEnabled();
}
