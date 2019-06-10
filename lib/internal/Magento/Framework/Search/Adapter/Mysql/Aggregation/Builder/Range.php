<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\Aggregation\Range as AggregationRange;
use Magento\Framework\Search\Request\Aggregation\RangeBucket;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Translate\AdapterInterface;

/**
 * MySQL search aggregation range builder.
 *
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 */
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
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param Metrics $metricsBuilder
     * @param ResourceConnection $resource
     */
    public function __construct(Metrics $metricsBuilder, ResourceConnection $resource)
    {
        $this->metricsBuilder = $metricsBuilder;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * @inheritdoc
     */
    public function build(
        DataProviderInterface $dataProvider,
        array $dimensions,
        RequestBucketInterface $bucket,
        Table $entityIdsTable
    ) {
        /** @var RangeBucket $bucket */
        $select = $dataProvider->getDataSet($bucket, $dimensions, $entityIdsTable);
        $metrics = $this->metricsBuilder->build($bucket);

        /** @var Select $fullQuery */
        $fullQuery = $this->connection
            ->select();
        $fullQuery->from(['main_table' => $select], null);
        $fullQuery = $this->generateCase($fullQuery, $bucket->getRanges());
        $fullQuery->columns($metrics);
        $fullQuery->group(new \Zend_Db_Expr('1'));

        return $dataProvider->execute($fullQuery);
    }

    /**
     * Generate case.
     *
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
        $cases = $this->connection
            ->getCaseSql('', $casesResults);
        $select->columns([RequestBucketInterface::FIELD_VALUE => $cases]);

        return $select;
    }
}
