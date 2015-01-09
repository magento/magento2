<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\Aggregation\Range as AggregationRange;
use Magento\Framework\Search\Request\Aggregation\RangeBucket;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

class Range implements BucketInterface
{
    const GREATER_THAN = '>=';
    const LOWER_THAN = '<';

    /**
     * @var Metrics
     */
    private $metricsBuilder;
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param Metrics $metricsBuilder
     * @param Resource $resource
     */
    public function __construct(Metrics $metricsBuilder, Resource $resource)
    {
        $this->metricsBuilder = $metricsBuilder;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        DataProviderInterface $dataProvider,
        array $dimensions,
        RequestBucketInterface $bucket,
        array $entityIds
    ) {
        /** @var RangeBucket $bucket */
        $select = $dataProvider->getDataSet($bucket, $dimensions);
        $metrics = $this->metricsBuilder->build($bucket);

        $select->where('main_table.entity_id IN (?)', $entityIds);

        /** @var Select $fullQuery */
        $fullQuery = $this->getConnection()
            ->select();
        $fullQuery->from(['main_table' => $select], null);
        $fullQuery = $this->generateCase($fullQuery, $bucket->getRanges());
        $fullQuery->columns($metrics);
        $fullQuery->group(new \Zend_Db_Expr('1'));

        return $dataProvider->execute($fullQuery);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }

    /**
     * @param Select $select
     * @param AggregationRange[] $ranges
     * @return Select
     */
    private function generateCase(Select $select, array $ranges)
    {
        $casesResults = [];
        $field = RequestBucketInterface::FIELD_VALUE;
        foreach ($ranges as $range) {
            $from = $range->getFrom();
            $to = $range->getTo();
            if ($from && $to) {
                $casesResults = array_merge(
                    $casesResults,
                    ["`{$field}` BETWEEN {$from} AND {$to}" => "'{$from}_{$to}'"]
                );
            } elseif ($from) {
                $casesResults = array_merge($casesResults, ["`{$field}` >= {$from}" => "'{$from}_*'"]);
            } elseif ($to) {
                $casesResults = array_merge($casesResults, ["`{$field}` < {$to}" => "'*_{$to}'"]);
            }
        }
        $cases = $this->getConnection()
            ->getCaseSql('', $casesResults);
        $select->columns([RequestBucketInterface::FIELD_VALUE => $cases]);

        return $select;
    }
}
