<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\Create\Items;

use Magento\Mtf\Client\Locator;
use Magento\GiftMessage\Test\Fixture\GiftMessage;

/**
 * Item product block on backend create order page.
 */
class ItemProduct extends \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items\ItemProduct
{
    /**
     * Selector for GiftOptions link.
     *
     * @var string
     */
    protected $giftOptionsLink = '[id^="gift_options_link"]';

    /**
     * Selector for order item GiftMessage form.
     *
     * @var string
     */
    protected $giftMessageForm = './/*[@role="dialog"][*[@id="gift_options_configure"]]';

    /**
     * Magento loader.
     *
     * @var string
     */
    protected $loader = '[data-role="loader"]';

    /**
     * Fill GiftMessage form.
     *
     * @param GiftMessage $giftMessage
     * @return void
     */
    public function fillGiftMessageForm(GiftMessage $giftMessage)
    {
        $giftOptionsLink = $this->_rootElement->find($this->giftOptionsLink);
        $giftOptionsLink->click();
        $giftMessageFormSelector = $this->giftMessageForm;
        $browser = $this->browser;
        $browser->waitUntil(
            function () use ($giftMessageFormSelector, $browser) {
                return $browser->find($giftMessageFormSelector, Locator::SELECTOR_XPATH)->isVisible() ? true : null;
            }
        );
        /** @var \Magento\GiftMessage\Test\Block\Adminhtml\Order\Create\Form $giftMessageForm */
        $giftMessageForm = $this->blockFactory->create(
            \Magento\GiftMessage\Test\Block\Adminhtml\Order\Create\Form::class,
            ['element' => $this->browser->find($this->giftMessageForm, Locator::SELECTOR_XPATH)]
        );
        $giftMessageForm->fill($giftMessage);
        $loader = $this->loader;
        $this->browser->waitUntil(
            function () use ($browser, $loader) {
                $element = $this->browser->find($loader);
                return $element->isVisible() == false ? true : null;
            }
        );
    }
}
