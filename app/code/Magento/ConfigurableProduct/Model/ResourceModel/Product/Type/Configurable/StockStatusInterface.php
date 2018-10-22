<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

/**
 * Interface StockStatusInterface
 * @api
 */
interface StockStatusInterface
{
    /**
     * @param int $productId
     * @return bool
     * @throws \Exception
     */
    public function isAllChildOutOfStock(int $productId): bool;
}
