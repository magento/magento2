<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\GiftMessage\Model\ResourceModel\Message;

/**
 * Gift Message collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\GiftMessage\Model\Message::class,
            \Magento\GiftMessage\Model\ResourceModel\Message::class
        );
    }
}
