<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Select;
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
    public function build(Select $baseQuery, RequestBucketInterface $bucket, array $entityIds)
    {
        /** @var RangeBucket $bucket */
        $metrics = $this->metricsBuilder->build($bucket);

        $baseQuery->where('main_table.entity_id IN (?)', $entityIds);

        /** @var Select $fullQuery */
        $fullQuery = $this->getConnection()->select();
        $fullQuery->from(['main_table' => $baseQuery], null);
        $fullQuery = $this->generateCase($fullQuery, $bucket->getRanges());
        $fullQuery->columns($metrics);
        $fullQuery->group(RequestBucketInterface::FIELD_VALUE);

        return $fullQuery;
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
            } elseif ($from && !$to) {
                $casesResults = array_merge($casesResults, ["`{$field}` >= {$from}" => "'{$from}_*'"]);
            } elseif (!$from && $to) {
                $casesResults = array_merge($casesResults, ["`{$field}` < {$to}" => "'*_{$to}'"]);
            }
        }
        $cases = $this->getConnection()->getCaseSql('', $casesResults);
        $select->columns([RequestBucketInterface::FIELD_VALUE => $cases]);
        return $select;
    }
}
