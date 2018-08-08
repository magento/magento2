<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Api;

/**
 * Perform bulk product source assignment
 *
 * @api
 */
interface BulkSourceAssignInterface
{
    /**
     * Run mass product to source assignment
     * @param string[] $skus
     * @param string[] $sourceCodes
     * @return int
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(array $skus, array $sourceCodes): int;
}
