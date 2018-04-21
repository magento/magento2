<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Exception\InputException;

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
     * @throws InputException
     */
    public function execute(array $skus);
}
