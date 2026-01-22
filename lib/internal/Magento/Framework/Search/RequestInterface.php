<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Search;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\QueryInterface;

/**
 * Search Request
 *
 * @api
 * @since 100.0.2
 */
interface RequestInterface
{
    /**
     * Get Name
     *
     * @return string
     */
    public function getName();

    /**
     * Get Index name
     *
     * @return string
     */
    public function getIndex();

    /**
     * Get all dimensions
     *
     * @return Dimension[]
     */
    public function getDimensions();

    /**
     * Get Aggregation Buckets
     *
     * @return RequestBucketInterface[]
     */
    public function getAggregation();

    /**
     * Get Main Request Query
     *
     * @return QueryInterface
     */
    public function getQuery();

    /**
     * Get From
     *
     * @return int|null
     */
    public function getFrom();

    /**
     * Get Size
     *
     * @return int|null
     */
    public function getSize();
}
