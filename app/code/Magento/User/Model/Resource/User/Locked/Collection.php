<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\Resource\User\Locked;

/**
 * Admin user collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\User\Model\Resource\User\Collection
{
    /**
     * Collection Init Select
     *
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter('lock_expires', ['notnull' => true]);
    }
}
