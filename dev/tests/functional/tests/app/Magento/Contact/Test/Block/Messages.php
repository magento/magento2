<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Message block on "Contact Us" page.
 */
class Messages extends Block
{
    /**
     * Message selector.
     *
     * @var string
     */
    private $message = '[data-bind*="message.text"]';

    /**
     * Get message which is present on the "Contact Us" page.
     *
     * @return string
     */
    public function getMessage()
    {
        $this->waitForElementVisible($this->message);

        return $this->_rootElement->find($this->message)->getText();
    }
}
