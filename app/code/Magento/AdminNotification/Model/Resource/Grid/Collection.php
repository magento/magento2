<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * AdminNotification Inbox model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\AdminNotification\Model\Resource\Grid;

class Collection extends \Magento\AdminNotification\Model\Resource\Inbox\Collection
{
    /**
     * Add remove filter
     *
     * @return \Magento\AdminNotification\Model\Resource\Grid\Collection|\Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addRemoveFilter();
        return $this;
    }
}
