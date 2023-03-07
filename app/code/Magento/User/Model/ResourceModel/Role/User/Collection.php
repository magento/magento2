<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\ResourceModel\Role\User;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\User\Model\ResourceModel\User as ResourceUser;
use Magento\User\Model\User as ModelUser;

/**
 * Admin role users collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ModelUser::class, ResourceUser::class);
    }

    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->where("user_id > 0");

        return $this;
    }
}
