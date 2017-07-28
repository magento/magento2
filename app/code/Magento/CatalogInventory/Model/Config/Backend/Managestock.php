<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Inventory Manage Stock Config Backend Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogInventory\Model\Config\Backend;

/**
 * Class \Magento\CatalogInventory\Model\Config\Backend\Managestock
 *
 * @since 2.0.0
 */
class Managestock extends AbstractValue
{
    /**
     * After change Catalog Inventory Manage Stock value process
     * @return $this
     * @since 2.0.0
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_stockIndexerProcessor->markIndexerAsInvalid();
        }
        return parent::afterSave();
    }
}
