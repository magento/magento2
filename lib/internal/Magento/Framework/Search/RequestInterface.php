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

    /**
     * Get Sort items
     *
     * @return array
     */
    public function getSort();
}
