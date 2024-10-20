<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Inventory Backorders Config Backend Model
 */
namespace Magento\CatalogInventory\Model\Config\Backend;

class Backorders extends AbstractValue
{
    /**
     * After change Catalog Inventory Backorders value process
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged() && (
                $this->getOldValue() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO
                || $this->getValue() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO
            )
        ) {
            $this->_stockIndexerProcessor->markIndexerAsInvalid();
        }
        return parent::afterSave();
    }
}
