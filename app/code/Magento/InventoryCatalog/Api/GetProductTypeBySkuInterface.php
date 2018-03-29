<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Api;

/**
 * Get product types id by product skus.
 *
 * @api
 */
interface GetProductTypeBySkuInterface
{
    /**
     * Returns product types id by product skus.
     *
     * @param array $skus
     * @return array (key: 'sku', value: 'product_type')
     */
    public function execute(array $skus);
}
