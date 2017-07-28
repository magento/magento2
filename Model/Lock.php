<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model;

/**
 * Class Lock to handle message lock transactions.
 * @since 2.1.0
 */
class Lock extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Class constructor
     *
     * @return void
     * @since 2.1.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\MessageQueue\Model\ResourceModel\Lock::class);
    }

    /**
     * Get message code
     *
     * @return string
     * @since 2.1.0
     */
    public function getMessageCode()
    {
        return $this->_getData('message_code');
    }

    /**
     * Set message code
     *
     * @param string $value
     * @return $this
     * @since 2.1.0
     */
    public function setMessageCode($value)
    {
        return $this->setData('message_code', $value);
    }

    /**
     * Get lock date
     *
     * @return string
     * @since 2.1.0
     */
    public function getCreatedAt()
    {
        return $this->_getData('created_at');
    }

    /**
     * Set lock date
     *
     * @param string $value
     * @return $this
     * @since 2.1.0
     */
    public function setCreatedAt($value)
    {
        return $this->setData('created_at', $value);
    }
}
