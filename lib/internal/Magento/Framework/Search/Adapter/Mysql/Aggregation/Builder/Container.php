<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Container
 *
 * @since 2.0.0
 */
class Container
{
    /**
     * @var BucketInterface[]
     * @since 2.0.0
     */
    private $buckets;

    /**
     * @param BucketInterface[] $buckets
     * @since 2.0.0
     */
    public function __construct(array $buckets)
    {
        $this->buckets = $buckets;
    }

    /**
     * @param string $bucketType
     * @return BucketInterface
     * @since 2.0.0
     */
    public function get($bucketType)
    {
        return $this->buckets[$bucketType];
    }
}
