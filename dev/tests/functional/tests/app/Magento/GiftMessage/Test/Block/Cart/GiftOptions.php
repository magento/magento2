<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Cart;

use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Mtf\Block\Form;

/**
 * Class GiftOptions
 * Add gift options on checkout cart order level
 */
class GiftOptions extends Form
{
    /**
     * Selector for gift message on order form
     *
     * @var string
     */
    protected $giftMessageOrderForm = ".gift-message fieldset";

    /**
     * Allow gift message on order level
     *
     * @var string
     */
    protected $allowGiftOptions = '.title';

    /**
     * Selector for apply Gift Message button on item
     *
     * @var string
     */
    protected $giftMessageOrderButton = ".action-update";

    /**
     * Selector for Gift Message Summary
     *
     * @var string
     */
    protected $giftMessageSummary = ".gift-message-summary";

    /**
     * Fill gift message form on order level
     *
     * @param GiftMessage $giftMessage
     * @return void
     */
    public function fillGiftMessageOrder(GiftMessage $giftMessage)
    {
        /** @var \Magento\GiftMessage\Test\Block\Cart\GiftOptions\GiftMessageForm $giftMessageForm */
        if ($giftMessage->getAllowGiftMessagesForOrder() === 'Yes') {
            $this->_rootElement->find($this->allowGiftOptions)->click();
            $giftMessageForm = $this->blockFactory->create(
                'Magento\GiftMessage\Test\Block\Cart\GiftOptions\GiftMessageForm',
                ['element' => $this->_rootElement->find($this->giftMessageOrderForm)]
            );
            $giftMessageForm->fill($giftMessage);
            $this->_rootElement->find($this->giftMessageOrderButton)->click();
            $this->waitForElementVisible($this->giftMessageSummary);
        }
    }
}
