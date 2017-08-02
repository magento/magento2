<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\QueryInterface;

/**
 * Search Request
 *
 * @codeCoverageIgnore
 * @api
 * @since 2.0.0
 */
class Request implements RequestInterface
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
    protected $index;

    /**
     * @var RequestBucketInterface[]
     * @since 2.0.0
     */
    protected $buckets;

    /**
     * Main query which represents the whole query hierarchy
     *
     * @var QueryInterface
     * @since 2.0.0
     */
    protected $query;

    /**
     * @var int|null
     * @since 2.0.0
     */
    protected $from;

    /**
     * @var int|null
     * @since 2.0.0
     */
    protected $size;

    /**
     * @var Dimension[]
     * @since 2.0.0
     */
    protected $dimensions;

    /**
     * @param string $name
     * @param string $indexName
     * @param QueryInterface $query
     * @param int|null $from
     * @param int|null $size
     * @param Dimension[] $dimensions
     * @param RequestBucketInterface[] $buckets
     * @since 2.0.0
     */
    public function __construct(
        $name,
        $indexName,
        QueryInterface $query,
        $from = null,
        $size = null,
        array $dimensions = [],
        array $buckets = []
    ) {
        $this->name = $name;
        $this->index = $indexName;
        $this->query = $query;
        $this->from = $from;
        $this->size = $size;
        $this->buckets = $buckets;
        $this->dimensions = $dimensions;
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
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAggregation()
    {
        return $this->buckets;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSize()
    {
        return $this->size;
    }
}
