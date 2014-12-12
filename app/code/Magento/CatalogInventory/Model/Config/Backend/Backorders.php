<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Catalog Inventory Backorders Config Backend Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
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
            $this->stockIndex->rebuild();
            $this->_stockIndexerProcessor->markIndexerAsInvalid();
        }
        return $this;
    }
}
