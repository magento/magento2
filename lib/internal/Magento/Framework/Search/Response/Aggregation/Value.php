<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response\Aggregation;

class Value
{
    /**
     * @var string|array
     */
    private $value;

    /**
     * @var array
     */
    private $metrics;

    /**
     * @param string|array $value
     * @param array $metrics
     */
    public function __construct($value, $metrics)
    {
        $this->value = $value;
        $this->metrics = $metrics;
    }

    /**
     * Get aggregation
     *
     * @return string|array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get metrics
     *
     * @return array
     */
    public function getMetrics()
    {
        return $this->metrics;
    }
}
