<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api\Data;

/**
 * Represents result of service Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface::execute
 *
 * @api
 */
interface ProductSalableResultInterface
{
    /**
     * @return bool
     */
    public function isSalable(): bool;

    /**
     * @return ProductSalabilityErrorInterface[]
     */
    public function getErrors(): array;
}
