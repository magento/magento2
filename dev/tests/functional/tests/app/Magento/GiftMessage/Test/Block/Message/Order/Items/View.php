<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Message\Order\Items;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Gift message block for order's items on order view page.
 */
class View extends Block
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
     * Selector for "Gift Message" button.
     *
     * @var string
     */
    protected $giftMessageButtonSelector = ".//tbody[contains(., '%s')]//a[contains(@id,'gift-message')]";

    /**
     * Selector for "Gift Message".
     *
     * @var string
     */
    protected $giftMessageForItemSelector = ".//tr[contains(., '%s')]/following-sibling::tr//*[@class='item-options']";

    /**
     * Get gift message for item.
     *
     * @param string $itemName
     * @return array
     */
    public function getGiftMessage($itemName)
    {
        if (!$this->giftMessageButtonIsVisible($itemName)) {
            return [];
        }
        $message = [];
        $labelsToSkip = [];
        $this->clickGiftMessageButton($itemName);
        $messageElement = $this->_rootElement->find(
            sprintf($this->giftMessageForItemSelector, $itemName),
            Locator::SELECTOR_XPATH
        );

        $labelsToSkip[] = $messageElement->find($this->giftMessageSenderSelector . ' strong')->getText();
        $labelsToSkip[] = $messageElement->find($this->giftMessageRecipientSelector . ' strong')->getText();
        $message['sender'] = $messageElement->find($this->giftMessageSenderSelector)->getText();
        $message['recipient'] = $messageElement->find($this->giftMessageRecipientSelector)->getText();
        $message['message'] = $messageElement->find($this->giftMessageTextSelector)->getText();
        $message = str_replace($labelsToSkip, '', $message);

        return $message;
    }

    /**
     * Click "Gift Message" for special item.
     *
     * @param string $itemName
     * @return void
     */
    protected function clickGiftMessageButton($itemName)
    {
        $this->_rootElement->find(
            sprintf($this->giftMessageButtonSelector, $itemName),
            Locator::SELECTOR_XPATH
        )->click();
    }

    /**
     * Click "Gift Message" for special item.
     *
     * @param string $itemName
     * @return bool
     */
    protected function giftMessageButtonIsVisible($itemName)
    {
        return $this->_rootElement->find(
            sprintf($this->giftMessageButtonSelector, $itemName),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }
}
