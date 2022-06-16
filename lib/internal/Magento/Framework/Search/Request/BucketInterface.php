<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

/**
 * Aggregation Bucket Interface
 *
 * @api
 * @since 100.0.2
 */
interface BucketInterface
{
    /**
     * #@+ Bucket Types
     */
    const TYPE_TERM = 'termBucket';

    const TYPE_RANGE = 'rangeBucket';

    const TYPE_DYNAMIC = 'dynamicBucket';

    const FIELD_VALUE = 'value';

    /**#@-*/

    /**
     * Get Type
     *
     * @return string
     */
    public function getType();

    /**
     * Get Field
     *
     * @return string
     */
    public function getField();

    /**
     * Get Metrics
     *
     * @return array
     */
    public function getMetrics();

    /**
     * Get Name
     *
     * @return string
     */
    public function getName();
}
