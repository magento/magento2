<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Test\Block\System;

use Magento\Mtf\Block\Block;

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
}
