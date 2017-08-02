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
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getType();

    /**
     * Get Field
     *
     * @return string
     * @since 2.0.0
     */
    public function getField();

    /**
     * Get Metrics
     *
     * @return array
     * @since 2.0.0
     */
    public function getMetrics();

    /**
     * Get Name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();
}
