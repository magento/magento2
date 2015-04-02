<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
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
    protected $closePopup = '.ui-dialog-titlebar-close';

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
