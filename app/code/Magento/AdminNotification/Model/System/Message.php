<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\System;

/**
 * @codeCoverageIgnore
 * @api
 * @since 2.0.0
 */
class Message extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\AdminNotification\Model\ResourceModel\System\Message::class);
    }

    /**
     * Check whether
     *
     * @return bool
     * @since 2.0.0
     */
    public function isDisplayed()
    {
        return true;
    }

    /**
     * Retrieve message text
     *
     * @return string
     * @since 2.0.0
     */
    public function getText()
    {
        return $this->getData('text');
    }

    /**
     * Retrieve message severity
     *
     * @return int
     * @since 2.0.0
     */
    public function getSeverity()
    {
        return $this->_getData('severity');
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     * @since 2.0.0
     */
    public function getIdentity()
    {
        return $this->_getData('identity');
    }
}
