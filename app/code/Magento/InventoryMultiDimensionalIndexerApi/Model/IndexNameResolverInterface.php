<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMultiDimensionalIndexerApi\Model;

/**
 * Resolve index name by IndexName object
 *
 * @api
 */
interface IndexNameResolverInterface
{
    /**
     * @param IndexName $indexName
     * @return string
     */
    public function resolveName(IndexName $indexName): string;
}
