<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response\Aggregation;

use Magento\Framework\Api\Search\AggregationValueInterface;

class Value implements AggregationValueInterface
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
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics()
    {
        return $this->metrics;
    }
}
