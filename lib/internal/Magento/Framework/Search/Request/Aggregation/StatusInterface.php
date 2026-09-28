<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
