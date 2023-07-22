<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\ResourceModel\User\Locked;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\User\Model\ResourceModel\User\Collection as UserCollection;

/**
 * Admin user collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends UserCollection
{
    /**
     * Collection Init Select
     *
     * @param AbstractDb $resource
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter('lock_expires', ['notnull' => true]);
    }
}
