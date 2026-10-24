<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Reports\Model\Event;

/**
 * Event type model
 *
 * @method string getEventName()
 * @method \Magento\Reports\Model\Event\Type setEventName(string $value)
 * @method int getCustomerLogin()
 * @method \Magento\Reports\Model\Event\Type setCustomerLogin(int $value)
 *
 * @api
 * @since 100.0.2
 */
class Type extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Reports\Model\ResourceModel\Event\Type::class);
    }
}
