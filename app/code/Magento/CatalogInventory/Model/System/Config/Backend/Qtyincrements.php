<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\System\Config\Backend;

use Magento\Framework\Exception\LocalizedException;

/**
 * Backend for qty increments
 */
class Qtyincrements extends \Magento\Framework\App\Config\Value
{
    /**
     * Validate data before save
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (floor($value) != $value) {
            throw new LocalizedException(__('Decimal qty increments is not allowed.'));
        }
    }
}
