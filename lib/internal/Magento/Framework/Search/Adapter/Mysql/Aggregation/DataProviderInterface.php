<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Dimension;

interface DataProviderInterface
{
    /**
     * @param BucketInterface $bucket
     * @param Dimension[] $dimensions
     * @param Table $entityIdsTable
     * @return Select
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    );

    /**
     * Executes query and return raw response
     *
     * @param Select $select
     * @return array
     */
    public function execute(Select $select);
}
