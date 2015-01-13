<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Inventory Manage Stock Config Backend Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogInventory\Model\Config\Backend;

class Managestock extends AbstractValue
{
    /**
     * After change Catalog Inventory Manage Stock value process
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->stockIndex->rebuild();
            $this->_stockIndexerProcessor->markIndexerAsInvalid();
        }
        return $this;
    }
}
