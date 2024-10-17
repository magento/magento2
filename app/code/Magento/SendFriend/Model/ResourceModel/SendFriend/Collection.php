<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Model\ResourceModel\SendFriend;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\SendFriend\Model\ResourceModel\SendFriend as ResourceSendFriend;
use Magento\SendFriend\Model\SendFriend as ModelSendFriend;

/**
 * SendFriend log resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ModelSendFriend::class,
            ResourceSendFriend::class
        );
    }
}
