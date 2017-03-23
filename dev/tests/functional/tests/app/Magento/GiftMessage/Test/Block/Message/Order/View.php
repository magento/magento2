<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Message\Order;

/**
 * Gift message block for order on order view page.
 */
class View extends \Magento\Sales\Test\Block\Order\View
{
    /**
     * Gift message sender selector.
     *
     * @var string
     */
    protected $giftMessageSenderSelector = "[class*='sender']";

    /**
     * Gift message recipient selector.
     *
     * @var string
     */
    protected $giftMessageRecipientSelector = "[class*='recipient']";

    /**
     * Gift message text selector.
     *
     * @var string
     */
    protected $giftMessageTextSelector = "[class*='message']";

    /**
     * Get gift message for order.
     *
     * @return array
     */
    public function getGiftMessage()
    {
        $message = [];
        $labelsToSkip = [];
        $labelsToSkip[] = $this->_rootElement->find($this->giftMessageSenderSelector . ' strong')->getText();
        $labelsToSkip[] = $this->_rootElement->find($this->giftMessageRecipientSelector . ' strong')->getText();
        $message['sender'] = $this->_rootElement->find($this->giftMessageSenderSelector)->getText();
        $message['recipient'] = $this->_rootElement->find($this->giftMessageRecipientSelector)->getText();
        $message['message'] = $this->_rootElement->find($this->giftMessageTextSelector)->getText();
        $message = str_replace($labelsToSkip, '', $message);

        return $message;
    }
}
