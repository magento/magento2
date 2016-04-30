<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Model\ResourceModel;

/**
 * @codeCoverageIgnore
 * Swatch Resource Model
 */
class Swatch extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_attribute_option_swatch', 'swatch_id');
    }

    /**
     * @param string $defaultValue
     * @param integer $id
     * @return void
     */
    public function saveDefaultSwatchOption($id, $defaultValue)
    {
        if ($defaultValue !== null) {
            $bind = ['default_value' => $defaultValue];
            $where = ['attribute_id = ?' => $id];
            $this->getConnection()->update($this->getTable('eav_attribute'), $bind, $where);
        }
    }
}
