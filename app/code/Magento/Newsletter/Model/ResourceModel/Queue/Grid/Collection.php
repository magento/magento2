<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter queue data grid collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Model\ResourceModel\Queue\Grid;

class Collection extends \Magento\Newsletter\Model\ResourceModel\Queue\Collection
{
    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addSubscribersInfo();
        return $this;
    }
}
