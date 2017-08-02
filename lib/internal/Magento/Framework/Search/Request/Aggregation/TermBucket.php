<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

use Magento\Framework\Search\Request\BucketInterface;

/**
 * Term Buckets
 * @since 2.0.0
 */
class TermBucket implements BucketInterface
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
    protected $metrics;

    /**
     * @param string $name
     * @param string $field
     * @param array $metrics
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct($name, $field, array $metrics)
    {
        $this->name = $name;
        $this->field = $field;
        $this->metrics = $metrics;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getType()
    {
        return BucketInterface::TYPE_TERM;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
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
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getMetrics()
    {
        return $this->metrics;
    }
}
