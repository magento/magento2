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
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\Tax\Model\Calculation\Rate\Title',
            'Magento\Tax\Model\ResourceModel\Calculation\Rate\Title'
        );
    }

    /**
     * Add rate id filter
     *
     * @param int $rateId
     * @return \Magento\Tax\Model\ResourceModel\Calculation\Rate\Title\Collection
     */
    public function loadByRateId($rateId)
    {
        $this->addFieldToFilter('main_table.tax_calculation_rate_id', $rateId);
        return $this->load();
    }
}
