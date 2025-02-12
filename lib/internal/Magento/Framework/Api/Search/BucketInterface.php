<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
