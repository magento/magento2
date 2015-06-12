<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Model\Resource\SendFriend;

/**
 * SendFriend log resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\SendFriend\Model\SendFriend', 'Magento\SendFriend\Model\Resource\SendFriend');
    }
}
