<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Facet Bucket
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
