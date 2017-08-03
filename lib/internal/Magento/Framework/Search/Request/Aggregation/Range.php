<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

/**
 * Range
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @since 2.0.0
 */
class Range
{
    /**
     * @var int|null
     * @since 2.0.0
     */
    protected $from;

    /**
     * @var int|null
     * @since 2.0.0
     */
    protected $to;

    /**
     * @param int|null $from
     * @param int|null $to
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getTo()
    {
        return $this->to;
    }
}
