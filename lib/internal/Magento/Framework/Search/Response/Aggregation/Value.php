<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response\Aggregation;

use Magento\Framework\Api\Search\AggregationValueInterface;

/**
 * Class \Magento\Framework\Search\Response\Aggregation\Value
 *
 * @since 2.0.0
 */
class Value implements AggregationValueInterface
{
    /**
     * @var string|array
     * @since 2.0.0
     */
    private $value;

    /**
     * @var array
     * @since 2.0.0
     */
    private $metrics;

    /**
     * @param string|array $value
     * @param array $metrics
     * @since 2.0.0
     */
    public function __construct($value, $metrics)
    {
        $this->value = $value;
        $this->metrics = $metrics;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getMetrics()
    {
        return $this->metrics;
    }
}
