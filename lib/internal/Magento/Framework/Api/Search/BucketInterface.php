<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Api\Search;

/**
 * Interface for facet Bucket
 *
 * @api
 */
interface BucketInterface
{
    /**
     * Get field name
     *
     * @return string
     */
    public function getName();

    /**
     * Get field values
     *
     * @return \Magento\Framework\Api\Search\AggregationValueInterface[]
     */
    public function getValues();
}
