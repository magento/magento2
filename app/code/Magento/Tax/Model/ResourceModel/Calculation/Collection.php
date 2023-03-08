<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel\Calculation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Tax\Model\Calculation as ModelCalculation;
use Magento\Tax\Model\ResourceModel\Calculation as ResourceCalculation;

/**
 * Tax Calculation Collection
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
        $this->_init(ModelCalculation::class, ResourceCalculation::class);
    }
}
