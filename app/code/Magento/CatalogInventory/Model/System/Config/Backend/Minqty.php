<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\System\Config\Backend;

/**
 * Minimum product qty backend model
 * @since 2.0.0
 */
class Minqty extends \Magento\Framework\App\Config\Value
{
    /**
     * Validate minimum product qty value
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $minQty = (int) $this->getValue() >= 0 ? (int) $this->getValue() : (int) $this->getOldValue();
        $this->setValue((string) $minQty);
        return $this;
    }
}
