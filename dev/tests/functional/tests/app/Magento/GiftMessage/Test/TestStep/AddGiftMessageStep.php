<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class AddGiftMessageStep
 * Add gift message to order or item
 */
class AddGiftMessageStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

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
     * @param CheckoutCart $checkoutCart
     * @param GiftMessage $giftMessage
     * @param array $products
     */
    public function __construct(CheckoutCart $checkoutCart, GiftMessage $giftMessage, array $products = [])
    {
        $this->checkoutCart = $checkoutCart;
        $this->giftMessage = $giftMessage;
        $this->products = $products;
    }

    /**
     * Add gift message to items and/or order.
     *
     * @return array
     */
    public function run()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getGiftMessagesItemBlock()->fillGiftMessageItem($this->giftMessage, $this->products);
        $this->checkoutCart->getGiftMessagesOrderBlock()->fillGiftMessageOrder($this->giftMessage, $this->products);

        return ['giftMessage' => $this->giftMessage];
    }
}
