<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Mtf\TestStep\TestStepInterface;

/**
 * Class AddGiftMessageStep
 * Add gift message to order or item
 */
class AddGiftMessageStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Gift message fixture
     *
     * @var GiftMessage
     */
    protected $giftMessage;

    /**
     * Array with products
     *
     * @var array
     */
    protected $products;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param GiftMessage $giftMessage
     * @param array $products
     */
    public function __construct(CheckoutOnepage $checkoutOnepage, GiftMessage $giftMessage, array $products = [])
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->giftMessage = $giftMessage;
        $this->products = $products;
    }

    /**
     * Add gift message to order
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutOnepage->getGiftMessagesBlock()->fillGiftMessage($this->giftMessage, $this->products);
    }
}
