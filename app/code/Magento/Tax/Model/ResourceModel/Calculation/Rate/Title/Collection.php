<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel\Calculation\Rate\Title;

/**
 * Tax Rate Title Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Tax\Model\Calculation\Rate\Title::class,
            \Magento\Tax\Model\ResourceModel\Calculation\Rate\Title::class
        );
    }

    /**
     * Add rate id filter
     *
     * @param int $rateId
     * @return \Magento\Tax\Model\ResourceModel\Calculation\Rate\Title\Collection
     * @since 2.0.0
     */
    public function loadByRateId($rateId)
    {
        $this->addFieldToFilter('main_table.tax_calculation_rate_id', $rateId);
        return $this->load();
    }
}
