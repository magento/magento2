<?php
/**
 * Newsletter subscriber grid collection
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\ResourceModel\Subscriber\Grid;

class Collection extends \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
{
    /**
     * Sets flag for customer info loading on load
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->showCustomerInfo(true)->addSubscriberTypeField()->showStoreInfo();
        return $this;
    }
}
