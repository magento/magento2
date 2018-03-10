<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

interface IsProductSalableResultInterface
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
