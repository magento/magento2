<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
