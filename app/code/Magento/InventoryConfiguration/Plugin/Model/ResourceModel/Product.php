<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfiguration\Plugin\Model\ResourceModel;

use Magento\Catalog\Model\Product as CatalogProduct;

class Product
{
    public function afterSave(CatalogProduct $subject, $result)
    {
        return $result;
    }
}