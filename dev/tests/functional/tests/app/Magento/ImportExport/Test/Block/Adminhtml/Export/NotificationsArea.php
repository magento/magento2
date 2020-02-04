<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Block\Adminhtml\Export;

use Magento\Backend\Test\Block\Widget\Grid;
use Magento\Mtf\Client\Locator;

/**
 * Notification messages area
 */
class NotificationsArea extends Grid
{
    /**
     * Notifications section drop down locator
     *
     * @var string
     */
    private $notificationsDropdown = '.notifications-action';

    /**
     * First notification description
     *
     * @var string
     */
    private $notificationDescription = '//li[@class="notifications-entry notifications-critical"][1]'
        . '/p[@class="notifications-entry-description"]';

    /**
     * Open notifications drop down
     *
     * @return void
     */
    public function openNotificationsDropDown()
    {
        $this->browser->find($this->notificationsDropdown)->click();
    }

    /**
     * Get latest notification message text
     *
     * @return string
     */
    public function getLatestMessage()
    {
        $this->waitForElementVisible($this->notificationDescription, Locator::SELECTOR_XPATH);
        return $this->_rootElement->find($this->notificationDescription, Locator::SELECTOR_XPATH)->getText();
    }
}
