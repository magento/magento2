<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

use Magento\Framework\Search\Request\BucketInterface;

/**
 * Dynamic Buckets
 */
class DynamicBucket implements BucketInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var array
     */
    protected $method;

    /**
     * @param string $name
     * @param string $field
     * @param string $method
     * @codeCoverageIgnore
     */
    public function __construct($name, $field, $method)
    {
        $this->name = $name;
        $this->field = $field;
        $this->method = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return BucketInterface::TYPE_DYNAMIC;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get method
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getMetrics()
    {
        return [];
    }
}
