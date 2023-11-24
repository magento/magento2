<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;

/**
 * Interface \Magento\Framework\Search\Dynamic\Algorithm\AlgorithmInterface
 *
 * @api
 */
interface AlgorithmInterface
{
    /**
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return array
     */
    public function getItems(BucketInterface $bucket, array $dimensions, EntityStorage $entityStorage);
}
