<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\System;

/**
 * @codeCoverageIgnore
 * @api
 * @since 100.0.2
 */
class Message extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\AdminNotification\Model\ResourceModel\System\Message::class);
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return true;
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        return $this->getData('text');
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return $this->_getData('severity');
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return $this->_getData('identity');
    }
}
