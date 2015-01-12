<?php
/**
 * Newsletter subscriber grid collection
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Resource\Subscriber\Grid;

class Collection extends \Magento\Newsletter\Model\Resource\Subscriber\Collection
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
