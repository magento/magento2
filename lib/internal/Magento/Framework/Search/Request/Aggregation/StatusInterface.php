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
 * @since 2.0.0
 */
interface StatusInterface
{
    /**
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled();
}
