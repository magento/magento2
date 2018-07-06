<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Plugin\Model\System\Config\Backend;

use Magento\CatalogInventory\Model\System\Config\Backend\Minqty;

/**
 * Allow min_qty to be assigned a value below 0.
 */
class MinqtyPlugin
{
    public function aroundBeforeSave(
        Minqty $subject,
        callable $proceed
    ) {
        $originalMinQty = $proceed();
        $originalMinQty->setValue($subject->getFieldsetDataValue('min_qty'));
        return $originalMinQty;
    }
}
