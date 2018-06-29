<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Api;

/**
 * Perform bulk product source un-assignment
 *
 * @api
 */
interface BulkSourceUnassignInterface
{
    /**
     * Run mass product to source un-assignment
     * @param string[] $skus
     * @param string[] $sourceCodes
     * @return int
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(array $skus, array $sourceCodes): int;
}
