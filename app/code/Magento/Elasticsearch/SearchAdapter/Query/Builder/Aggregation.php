<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

/**
 * @api
 * @since 100.1.0
 */
class Aggregation
{
    /**
     * @var FieldMapperInterface
     * @since 100.1.0
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
     * @since 100.1.0
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
     * @return array
     * @since 100.1.0
     */
    protected function buildBucket(
        array $searchQuery,
        BucketInterface $bucket
    ) {
        $field = $this->fieldMapper->getFieldName($bucket->getField());
        switch ($bucket->getType()) {
            case BucketInterface::TYPE_TERM:
                $searchQuery['body']['aggregations'][$bucket->getName()]= [
                    'terms' => [
                        'field' => $field,
                    ],
                ];
                break;
            case BucketInterface::TYPE_DYNAMIC:
                $searchQuery['body']['aggregations'][$bucket->getName()]= [
                    'extended_stats' => [
                        'field' => $field,
                    ],
                ];
                break;
        }
        return $searchQuery;
    }
}
