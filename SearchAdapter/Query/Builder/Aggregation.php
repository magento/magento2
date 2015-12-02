<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\SearchAdapter\FieldMapperInterface;

class Aggregation
{
    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @param FieldMapperInterface $fieldMapper
     */
    public function __construct(
        FieldMapperInterface $fieldMapper
    ) {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * Build aggregation query for request
     *
     * @param RequestInterface $request
     * @param array $searchQuery
     * @return array
     */
    public function build(
        RequestInterface $request,
        array $searchQuery
    ) {
        $buckets = $request->getAggregation();
        foreach ($buckets as $bucket) {
            $searchQuery = $this->buildBucket($searchQuery, $bucket);
        }
        return $searchQuery;
    }

    /**
     * Build aggregation query for bucket
     *
     * @param array $searchQuery
     * @param BucketInterface $bucket
     * @return void
     */
    protected function buildBucket(
        array $searchQuery,
        BucketInterface $bucket
    ) {
        switch ($bucket->getType()) {
            case BucketInterface::TYPE_TERM:
                $searchQuery['body']['aggregations'][$bucket->getName()]= [
                    'terms' => [
                        'field' => $this->fieldMapper->getFieldName($bucket->getField()),
                    ],
                ];
                break;
            case BucketInterface::TYPE_DYNAMIC:
                break;
        }
        return $searchQuery;
    }
}
