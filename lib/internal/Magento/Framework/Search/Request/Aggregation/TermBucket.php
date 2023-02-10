<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var array
     */
    private $parameters;

    /**
     * @param string $name
     * @param string $field
     * @param array $metrics
     * @param array $parameters
     * @codeCoverageIgnore
     */
    public function __construct(
        $name,
        $field,
        array $metrics,
        array $parameters = []
    ) {
        $this->name = $name;
        $this->field = $field;
        $this->metrics = $metrics;
        $this->parameters = $parameters;
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

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
