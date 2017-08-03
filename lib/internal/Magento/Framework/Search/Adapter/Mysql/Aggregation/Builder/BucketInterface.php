<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Request\Dimension;

/**
 * Interface \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\BucketInterface
 *
 * @since 2.0.0
 */
interface BucketInterface
{
    /**
     * @param DataProviderInterface $dataProvider
     * @param Dimension[] $dimensions
     * @param RequestBucketInterface $bucket
     * @param Table $entityIdsTable
     * @return array
     * @since 2.0.0
     */
    public function build(
        DataProviderInterface $dataProvider,
        array $dimensions,
        RequestBucketInterface $bucket,
        Table $entityIdsTable
    );
}
