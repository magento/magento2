<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Term
 *
 * @since 2.0.0
 */
class Term implements BucketInterface
{
    /**
     * @var Metrics
     * @since 2.0.0
     */
    private $metricsBuilder;

    /**
     * @param Metrics $metricsBuilder
     * @since 2.0.0
     */
    public function __construct(Metrics $metricsBuilder)
    {
        $this->metricsBuilder = $metricsBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function build(
        DataProviderInterface $dataProvider,
        array $dimensions,
        RequestBucketInterface $bucket,
        Table $entityIdsTable
    ) {
        $metrics = $this->metricsBuilder->build($bucket);

        $select = $dataProvider->getDataSet($bucket, $dimensions, $entityIdsTable);
        $select->columns($metrics);
        $select->group(RequestBucketInterface::FIELD_VALUE);

        return $dataProvider->execute($select);
    }
}
