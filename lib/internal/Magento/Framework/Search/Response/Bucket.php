<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Response;

use Magento\Framework\Api\Search\AggregationValueInterface;
use Magento\Framework\Api\Search\BucketInterface;
use Magento\Framework\Search\Response\Aggregation\Value;

/**
 * Facet Bucket
 * @since 2.0.0
 */
class Bucket implements BucketInterface
{
    /**
     * Field name
     *
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * Field values
     *
     * @var mixed
     * @since 2.0.0
     */
    protected $values;

    /**
     * @param string $name
     * @param \Magento\Framework\Api\Search\AggregationValueInterface[] $values
     * @since 2.0.0
     */
    public function __construct($name, $values)
    {
        $this->name = $name;
        $this->values = $values;
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
     * @since 2.0.0
     */
    public function getValues()
    {
        return $this->values;
    }
}
