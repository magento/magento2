<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

/**
 * Limit configuration
 */
class LimitConfig
{
    /**
     * @var int
     */
    private int $limit;

    /**
     * @var int
     */
    private int $period;

    /**
     * @param int $limit
     * @param int $period
     */
    public function __construct(int $limit, int $period)
    {
        $this->limit = $limit;
        $this->period = $period;
    }

    /**
     * Requests per period
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Period in seconds
     *
     * @return int
     */
    public function getPeriod(): int
    {
        return $this->period;
    }
}
