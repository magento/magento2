<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $value = (int) $this->getValue();
        $minQty = !empty($value) ? $value : 0;
        $this->setValue((string) $minQty);
        return $this;
    }
}
