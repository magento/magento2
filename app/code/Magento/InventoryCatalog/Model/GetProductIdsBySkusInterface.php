<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

/**
 * Provides all product SKUs by ProductIds. Key is sku, value is product id
 * @api
 */
interface GetProductIdsBySkusInterface
{
    /**
     * @param array $skus
     * @return array
     */
    public function execute(array $skus): array;
}
