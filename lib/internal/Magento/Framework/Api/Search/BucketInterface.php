<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Search;

/**
 * Facet Bucket
 * @since 2.0.0
 */
interface BucketInterface
{
    /**
     * Get field name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Get field values
     *
     * @return \Magento\Framework\Api\Search\AggregationValueInterface[]
     * @since 2.0.0
     */
    public function getValues();
}
