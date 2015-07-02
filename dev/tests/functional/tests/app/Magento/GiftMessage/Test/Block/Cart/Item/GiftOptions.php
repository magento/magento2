<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Cart\Item;

use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Add gift options on checkout cart item level
 */
class GiftOptions extends Form
{
    /**
     * Selector for gift message on item form
     *
     * @var string
     */
    protected $giftMessageItemForm = '//div[@class="gift-message"]//fieldset[ancestor::tbody[contains(.,"%s")]]';

    /**
     * Allow Gift Options for items
     *
     * @var string
     */
    protected $allowGiftOptions = '//a[contains(@class,"action-gift")][ancestor::tbody[contains(.,"%s")]]';

    /**
     * Selector for apply Gift Message button on order
     *
     * @var string
     */
    protected $giftMessageItemButton = ".action-update";

    /**
     * Selector for Gift Message Summary
     *
     * @var string
     */
    protected $giftMessageSummary = '//div[@class="gift-message-summary"][ancestor::tbody[contains(.,"%s")]]';

    /**
     * Fill gift message form on item level
     *
     * @param GiftMessage $giftMessage
     * @param array $products
     * @return void
     */
    public function fillGiftMessageItem(GiftMessage $giftMessage, $products = [])
    {
        /** @var \Magento\GiftMessage\Test\Block\Cart\GiftOptions\GiftMessageForm $giftMessageForm */
        if ($giftMessage->getAllowGiftOptionsForItems() === 'Yes') {
            foreach ($products as $product) {
                if ($product->getIsVirtual() !== 'Yes') {
                    $this->_rootElement->find(
                        sprintf($this->allowGiftOptions, $product->getName()),
                        Locator::SELECTOR_XPATH
                    )->click();
                    $giftMessageForm = $this->blockFactory->create(
                        'Magento\GiftMessage\Test\Block\Cart\GiftOptions\GiftMessageForm',
                        [
                            'element' => $this->_rootElement->find(
                                sprintf($this->giftMessageItemForm, $product->getName()),
                                Locator::SELECTOR_XPATH
                            )
                        ]
                    );
                    $giftMessageForm->fill($giftMessage);
                    $this->_rootElement->find($this->giftMessageItemButton)->click();
                    $this->waitForElementVisible(
                        sprintf($this->giftMessageSummary, $product->getName()),
                        Locator::SELECTOR_XPATH
                    );
                }
            }
        }
    }
}
