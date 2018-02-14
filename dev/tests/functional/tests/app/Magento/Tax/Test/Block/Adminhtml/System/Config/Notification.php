<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Block\Adminhtml\System\Config;

use Magento\Mtf\Block\Block;

/**
 * Admin notification block.
 */
class Notification extends Block
{
    /**
     * Locator value for opening global messages pupup.
     *
     * @var string
     */
    private $messageLink = '.message-link';

    /**
     * Open global messages popup.
     *
     * @return void
     */
    public function openNotificationPopup()
    {
        $messageLink = $this->_rootElement->find($this->messageLink);
        if ($messageLink->isVisible()) {
            $messageLink->click();
        }
    }
}
