<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter queue data grid collection
 */
namespace Magento\Newsletter\Model\ResourceModel\Queue\Grid;

class Collection extends \Magento\Newsletter\Model\ResourceModel\Queue\Collection
{
    /**
     * Init select method
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addSubscribersInfo();
        return $this;
    }
}
