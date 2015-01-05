<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Collection of unread notifications
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\AdminNotification\Model\Resource\Inbox\Collection;

class Unread extends \Magento\AdminNotification\Model\Resource\Inbox\Collection
{
    /**
     * Init collection select
     *
     * @return \Magento\AdminNotification\Model\Resource\Inbox\Collection\Unread
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFilter('is_remove', 0);
        $this->addFilter('is_read', 0);
        $this->setOrder('date_added');
        return $this;
    }
}
