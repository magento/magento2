<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

class Term implements BucketInterface
{
    /**
     * @var Metrics
     */
    private $metricsBuilder;

    /**
     * @param Metrics $metricsBuilder
     */
    public function __construct(Metrics $metricsBuilder)
    {
        $this->metricsBuilder = $metricsBuilder;
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
        $metrics = $this->metricsBuilder->build($bucket);

        $select = $dataProvider->getDataSet($bucket, $dimensions);
        $select->where('main_table.entity_id IN (?)', $entityIds);
        $select->columns($metrics);
        $select->group(RequestBucketInterface::FIELD_VALUE);

        return $dataProvider->execute($select);
    }
}
