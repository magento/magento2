<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Test\Block\System;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Global messages block.
 */
class Messages extends Block
{
    /**
     * Locator for close message block.
     *
     * @var string
     */
    protected $closePopup = '[data-role="closeBtn"]';

    /**
     * Locator for popup text.
     *
     * @var string
     */
    protected $popupText = ".//*[@id='system_messages_list']/ul/li";

    /**
     * Close popup block.
     *
     * @return void
     */
    public function closePopup()
    {
        if ($this->_rootElement->isVisible()) {
            $this->_rootElement->find($this->closePopup)->click();
        }
    }

    /**
     * Get pop up text.
     *
     * @return string
     */
    public function getPopupText()
    {
        return $this->_rootElement->find($this->popupText, Locator::SELECTOR_XPATH)->getText();
    }
}
