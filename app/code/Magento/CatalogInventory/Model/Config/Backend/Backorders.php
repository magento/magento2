<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Inventory Backorders Config Backend Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogInventory\Model\Config\Backend;

/**
 * Class \Magento\CatalogInventory\Model\Config\Backend\Backorders
 *
 * @since 2.0.0
 */
class Backorders extends AbstractValue
{
    /**
     * After change Catalog Inventory Backorders value process
     *
     * @return $this
     * @since 2.0.0
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
