<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Client\Locator;

/**
 * One page checkout status review block.
 */
class Review extends AbstractReview
{
    /**
     * Review gift card line locator.
     *
     * @var string
     */
    private $giftCardTotalSelector = '//div[contains(@class, "opc-block-summary")]//tr[contains(@class, "giftcard")]';

    /**
     * Return if gift card is applied.
     *
     * @return bool
     */
    public function isGiftCardApplied()
    {
        $this->waitForElementNotVisible($this->waitElement);

        return $this->_rootElement->find($this->giftCardTotalSelector, Locator::SELECTOR_XPATH)->isVisible();
    }
}
