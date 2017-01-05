<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Request\Dimension;

interface BucketBuilderInterface
{
    /**
     * @param RequestBucketInterface $bucket
     * @param Dimension[] $dimensions
     * @param array $queryResult
     * @param DataProviderInterface $dataProvider
     * @return array
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    );
}
