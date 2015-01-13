<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

class Container
{
    /**
     * @var BucketInterface[]
     */
    private $buckets;

    /**
     * @param BucketInterface[] $buckets
     */
    public function __construct(array $buckets)
    {
        $this->buckets = $buckets;
    }

    /**
     * @param string $bucketType
     * @return BucketInterface
     */
    public function get($bucketType)
    {
        return $this->buckets[$bucketType];
    }
}
