<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Block\Adminhtml\System\Config;

use Magento\AdminNotification\Test\Block\System\Messages;

/**
 * @inheritdoc
 */
class NotificationPopup extends Messages
{
    /**
     * Locator value for system messages list.
     *
     * @var string
     */
    private $messageSystemList = '.message-system-list';

    /**
     * Get all messages from global messages block.
     *
     * @return string
     */
    public function getNotificationMessage()
    {
        $message = '';
        if ($this->_rootElement->isVisible()) {
            $message = $this->_rootElement->find($this->messageSystemList)->getText();
        }

        return $message;
    }
}
