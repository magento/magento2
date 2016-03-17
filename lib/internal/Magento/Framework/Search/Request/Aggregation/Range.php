<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

/**
 * Range
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Range
{
    /**
     * @var int|null
     */
    protected $from;

    /**
     * @var int|null
     */
    protected $to;

    /**
     * @param int|null $from
     * @param int|null $to
     * @codeCoverageIgnore
     */
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get From
     *
     * @return int|null
     * @codeCoverageIgnore
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Get To
     *
     * @return int|null
     * @codeCoverageIgnore
     */
    public function getTo()
    {
        return $this->to;
    }
}
