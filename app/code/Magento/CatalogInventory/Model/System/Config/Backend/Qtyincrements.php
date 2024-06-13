<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
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
            throw new LocalizedException(
                __("Quantity increments can't use decimals. Enter a new increment and try again.")
            );
        }
    }
}
