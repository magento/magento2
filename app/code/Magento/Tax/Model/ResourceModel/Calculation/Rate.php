<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax rate resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\ResourceModel\Calculation;

/**
 * Class \Magento\Tax\Model\ResourceModel\Calculation\Rate
 *
 * @since 2.0.0
 */
class Rate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('tax_calculation_rate', 'tax_calculation_rate_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => ['code'], 'title' => __('Code')]];
        return $this;
    }

    /**
     * Delete all rates
     *
     * @return $this
     * @since 2.0.0
     */
    public function deleteAllRates()
    {
        $this->getConnection()->delete($this->getMainTable());
        return $this;
    }

    /**
     * Check if this rate exists in rule
     *
     * @param  int $rateId
     * @return array
     * @since 2.0.0
     */
    public function isInRule($rateId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('tax_calculation'),
            ['tax_calculation_rate_id']
        )->where(
            'tax_calculation_rate_id = ?',
            $rateId
        );
        return $connection->fetchCol($select);
    }
}
