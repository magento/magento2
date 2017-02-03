<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Items;

use Magento\Mtf\Client\Locator;
use Magento\GiftMessage\Test\Fixture\GiftMessage;

/**
 * Class ItemProduct
 * Item product block on SalesOrderView page.
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
     * Get GiftMessage form data.
     *
     * @param GiftMessage $giftMessage
     * @return array
     */
    public function getGiftMessageFormData(GiftMessage $giftMessage)
    {
        $giftOptionsLink = $this->_rootElement->find($this->giftOptionsLink);
        if ($giftOptionsLink->isVisible()) {
            $giftOptionsLink->click();
        }
        $giftMessageFormSelector = $this->giftMessageForm;
        $browser = $this->browser;
        $browser->waitUntil(
            function () use ($giftMessageFormSelector, $browser) {
                return $browser->find($giftMessageFormSelector, Locator::SELECTOR_XPATH)->isVisible() ? true : null;
            }
        );
        /** @var \Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Form $giftMessageForm */
        $giftMessageForm = $this->blockFactory->create(
            'Magento\GiftMessage\Test\Block\Adminhtml\Order\View\Form',
            ['element' => $this->browser->find($this->giftMessageForm, Locator::SELECTOR_XPATH)]
        );
        $data = $giftMessageForm->getData($giftMessage);
        $giftMessageForm->closeDialog();
        return $data;
    }
}
