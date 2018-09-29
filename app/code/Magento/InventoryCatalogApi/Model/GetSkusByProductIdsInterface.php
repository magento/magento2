<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Provides all product SKUs by ProductIds. Key is product id, value is sku
 * @api
 */
interface GetSkusByProductIdsInterface
{
    /**
     * @param array $productIds
     * @return array
     * @throws NoSuchEntityException
     */
    public function execute(array $productIds): array;
}
