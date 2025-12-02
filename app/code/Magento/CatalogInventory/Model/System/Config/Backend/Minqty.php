<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Model\System\Config\Backend;

/**
 * Minimum product qty backend model
 */
class Minqty extends \Magento\Framework\App\Config\Value
{
    /**
     * Validate minimum product qty value
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $minQty = (int) $this->getValue() >= 0 ? (int) $this->getValue() : (int) $this->getOldValue();
        $this->setValue((string) $minQty);
        return $this;
    }
}
