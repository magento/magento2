<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Message;

use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Mtf\Block\Form;
use Mtf\Client\Element\Locator;

/**
 * Class Inline
 * Checkout add gift options
 */
class Inline extends Form
{
    /**
     * Selector for gift message on item form
     *
     * @var string
     */
    protected $giftMessageItemForm = ".//li[@class='item'][contains(.,'%s')]/div[@class='options']";

    /**
     * Selector for gift message on order form
     *
     * @var string
     */
    protected $giftMessageOrderForm = ".gift-messages-order";

    /**
     * Selector for "Gift Message" button on order
     *
     * @var string
     */
    protected $giftMessageItemButton = ".//li[@class='item'][contains(.,'%s')]/div[@class='options']/a";

    /**
     * Selector for "Gift Message" button on item
     *
     * @var string
     */
    protected $giftMessageOrderButton = "#allow-gift-options-for-order-container > a";

    /**
     * Fill gift message form
     *
     * @param GiftMessage $giftMessage
     * @param array $products
     * @return void
     */
    public function fillGiftMessage(GiftMessage $giftMessage, $products = [])
    {
        $this->fill($giftMessage);

        /** @var \Magento\GiftMessage\Test\Block\Message\Inline\GiftMessageForm $giftMessageForm */
        if ($giftMessage->getAllowGiftMessagesForOrder() === 'Yes') {
            $this->_rootElement->find($this->giftMessageOrderButton)->click();
            $giftMessageForm = $this->blockFactory->create(
                'Magento\GiftMessage\Test\Block\Message\Inline\GiftMessageForm',
                ['element' => $this->_rootElement->find($this->giftMessageOrderForm)]
            );
            $giftMessageForm->fill($giftMessage);
        }

        if ($giftMessage->getAllowGiftOptionsForItems() === 'Yes') {
            foreach ($products as $product) {
                $this->_rootElement->find(
                    sprintf($this->giftMessageItemButton, $product->getName()),
                    Locator::SELECTOR_XPATH
                )->click();
                $giftMessageForm = $this->blockFactory->create(
                    'Magento\GiftMessage\Test\Block\Message\Inline\GiftMessageForm',
                    [
                        'element' => $this->_rootElement->find(
                            sprintf($this->giftMessageItemForm, $product->getName()),
                            Locator::SELECTOR_XPATH
                        )
                    ]
                );
                $giftMessageForm->fill($giftMessage);
            }
        }
    }
}
