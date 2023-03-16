<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Rule\Customer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\SalesRule\Model\ResourceModel\Rule\Customer as ResourceRuleCustomer;
use Magento\SalesRule\Model\Rule\Customer as ModelRuleCustomer;

/**
 * SalesRule Model Resource Rule Customer_Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection
{
    /**
     * Collection constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            ModelRuleCustomer::class,
            ResourceRuleCustomer::class
        );
    }
}
