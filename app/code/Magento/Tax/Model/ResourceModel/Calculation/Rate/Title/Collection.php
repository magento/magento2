<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel\Calculation\Rate\Title;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Tax\Model\Calculation\Rate\Title as ModelCalculationRateTitle;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\Title as ResourceCalculationRateTitle;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\Title\Collection as CalculationRateTitleCollection;

/**
 * Tax Rate Title Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ModelCalculationRateTitle::class,
            ResourceCalculationRateTitle::class
        );
    }

    /**
     * Add rate id filter
     *
     * @param int $rateId
     * @return CalculationRateTitleCollection
     */
    public function loadByRateId($rateId)
    {
        $this->addFieldToFilter('main_table.tax_calculation_rate_id', $rateId);
        return $this->load();
    }
}
