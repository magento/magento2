<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Cart;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Block for text of empty cart.
 */
class CartEmpty extends Block
{
    /**
     * Selector for link "here" to main page.
     *
     * @var string
     */
    private $linkToMainPage = 'p a';

    /**
     * CSS selector for message text.
     *
     * @var string
     */
    private $messageText = 'p';

    /**
     * Get test for empty cart.
     *
     * @return string
     */
    public function getText()
    {
        $result = [];
        foreach ($this->_rootElement->getElements($this->messageText) as $item) {
            $result[] = str_replace("\n", ' ', $item->getText());
        }

        return implode(' ', $result);
    }

    /**
     * Click link to main page.
     *
     * @return void
     */
    public function clickLinkToMainPage()
    {
        $this->_rootElement->find($this->linkToMainPage)->click();
    }
}
