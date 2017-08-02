<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * Interface \Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface
 *
 * @since 2.0.0
 */
interface AlgorithmInterface
{
    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return array
     * @since 2.0.0
     */
    public function getItems(BucketInterface $bucket, array $dimensions, EntityStorage $entityStorage);
}
