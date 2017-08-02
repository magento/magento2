<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

use Magento\Framework\Search\Request\BucketInterface;

/**
 * Dynamic Buckets
 * @api
 * @since 2.0.0
 */
class DynamicBucket implements BucketInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $field;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $method;

    /**
     * @param string $name
     * @param string $field
     * @param string $method
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct($name, $field, $method)
    {
        $this->name = $name;
        $this->field = $field;
        $this->method = $method;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getType()
    {
        return BucketInterface::TYPE_DYNAMIC;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getMetrics()
    {
        return [];
    }
}
