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
 * @api
 * @since 2.0.0
 */
interface RequestInterface
{
    /**
     * Get Name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Get Index name
     *
     * @return string
     * @since 2.0.0
     */
    public function getIndex();

    /**
     * Get all dimensions
     *
     * @return Dimension[]
     * @since 2.0.0
     */
    public function getDimensions();

    /**
     * Get Aggregation Buckets
     *
     * @return RequestBucketInterface[]
     * @since 2.0.0
     */
    public function getAggregation();

    /**
     * Get Main Request Query
     *
     * @return QueryInterface
     * @since 2.0.0
     */
    public function getQuery();

    /**
     * Get From
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getFrom();

    /**
     * Get Size
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getSize();
}
