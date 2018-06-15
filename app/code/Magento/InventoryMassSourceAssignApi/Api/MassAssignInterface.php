<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssignApi\Api;

/**
 * Perform mass product source assignment
 *
 * @api
 */
interface MassAssignInterface
{
    /**
     * Run mass product to source assignment
     * @param array $skus
     * @param array $sourceCodes
     * @return int
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(array $skus, array $sourceCodes): int;
}
