<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Model\ResourceModel;

/**
 * Swatch Resource Model
 *
 * @codeCoverageIgnore
 * @api
 * @since 100.0.2
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
     * Update default swatch option value.
     *
     * @param integer $id
     * @param string $defaultValue
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

    /**
     * Cleaned swatch option values when switching to dropdown input type.
     *
     * @param array $optionIDs
     * @param int $type
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.4
     */
    public function clearSwatchOptionByOptionIdAndType($optionIDs, $type = null)
    {
        if (count($optionIDs)) {
            foreach ($optionIDs as $optionId) {
                $where = ['option_id = ?' => $optionId];
                if ($type !== null) {
                    $where['type = ?'] = $type;
                }
                $this->getConnection()->delete($this->getMainTable(), $where);
            }
        }
    }
}
