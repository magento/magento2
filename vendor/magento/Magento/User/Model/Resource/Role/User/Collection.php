<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\User\Model\Resource\Role\User;

/**
 * Admin role users collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\User\Model\User', 'Magento\User\Model\Resource\User');
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
