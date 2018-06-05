<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

/**
 * Get product types id by product skus.
 *
 * @api
 */
interface GetProductTypesBySkusInterface
{
    /**
     * @param array $skus
     * @return array (key: 'sku', value: 'product_type')
     */
    public function execute(array $skus);
}
