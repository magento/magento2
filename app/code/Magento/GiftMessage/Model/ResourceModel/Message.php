<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\ResourceModel;

/**
 * Gift Message resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('gift_message', 'gift_message_id');
    }
}
