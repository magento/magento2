<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

use Magento\Framework\Search\Request\BucketInterface;

/**
 * Term Buckets
 */
class TermBucket implements BucketInterface
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
    protected $metrics;

    /**
     * @param string $name
     * @param string $field
     * @param array $metrics
     * @codeCoverageIgnore
     */
    public function __construct($name, $field, array $metrics)
    {
        $this->name = $name;
        $this->field = $field;
        $this->metrics = $metrics;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return BucketInterface::TYPE_TERM;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
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
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getMetrics()
    {
        return $this->metrics;
    }
}
