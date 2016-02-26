<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

class Log extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Framework\MessageQueue\ResourceModel\Log');
    }

    /**
     * Get message code
     *
     * @return string
     */
    public function getMessageCode()
    {
        return $this->_getData('message_code');
    }

    /**
     * Set message code
     *
     * @param string $value
     * @return string
     */
    public function setMessageCode($value)
    {
        return $this->setData('message_code', $value);
    }
}
