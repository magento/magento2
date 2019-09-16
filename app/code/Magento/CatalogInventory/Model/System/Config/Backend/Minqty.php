<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\System\Config\Backend;

use Magento\CatalogInventory\Model\Stock;

/**
 * Minimum product qty backend model.
 */
class Minqty extends \Magento\Framework\App\Config\Value
{
    /**
     * Validate minimum product qty value.
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $minQty = (float)$this->getValue();

        /**
         * As described in the documentation if the Backorders Option is disabled
         * it is recommended to set the Out Of Stock Threshold to a positive number.
         * That's why to clarify the logic to the end user the code below prevent him to set a negative number so such
         * a number will turn to zero.
         * @see https://docs.magento.com/m2/ce/user_guide/catalog/inventory-backorders.html
         */
        if ($this->getFieldsetDataValue("backorders") == Stock::BACKORDERS_NO && $minQty < 0) {
            $minQty = 0;
        }

        $this->setValue((string) $minQty);

        return $this;
    }
}
