<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

/**
 * Aggregation Factory
 */
class AggregationFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create Aggregation instance
     *
     * @param array $rawAggregation
     * @return \Magento\Framework\Search\Response\Aggregation
     */
    public function create(array $rawAggregation)
    {
        $buckets = [];
        foreach ($rawAggregation as $rawBucketName => $rawBucket) {
            /** @var \Magento\Framework\Search\Response\Bucket[] $buckets */
            $buckets[$rawBucketName] = $this->objectManager->create(
                'Magento\Framework\Search\Response\Bucket',
                [
                    'name' => $rawBucketName,
                    'values' => $this->prepareValues((array)$rawBucket)
                ]
            );
        }
        return $this->objectManager->create('Magento\Framework\Search\Response\Aggregation', ['buckets' => $buckets]);
    }

    /**
     * Prepare values list
     *
     * @param array $values
     * @return \Magento\Framework\Search\Response\Aggregation\Value[]
     */
    private function prepareValues(array $values)
    {
        $valuesObjects = [];
        foreach ($values as $name => $value) {
            $valuesObjects[] = $this->objectManager->create(
                'Magento\Framework\Search\Response\Aggregation\Value',
                [
                    'value' => $name,
                    'metrics' => $value,
                ]
            );
        }
        return $valuesObjects;
    }
}
