<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\TestStep;

use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Mtf\TestStep\TestStepInterface;

/**
 * Class AddGiftMessageBackendStep
 * Add gift message to order or item on backend
 */
class AddGiftMessageBackendStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Gift message fixture.
     *
     * @var GiftMessage
     */
    protected $giftMessage;

    /**
     * Array with products.
     *
     * @var array
     */
    protected $products;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param GiftMessage $giftMessage
     * @param array $products
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        GiftMessage $giftMessage,
        array $products = []
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->giftMessage = $giftMessage;
        $this->products = $products;
    }

    /**
     * Add gift message to backend order.
     *
     * @return array
     */
    public function run()
    {
        if ($this->giftMessage->getAllowGiftMessagesForOrder()) {
            $this->orderCreateIndex->getGiftMessageForOrderBlock()->fill($this->giftMessage);
        }
        if ($this->giftMessage->getAllowGiftOptionsForItems()) {
            $this->orderCreateIndex->getCreateGiftMessageBlock()
                ->fillGiftMessageForItems($this->products, $this->giftMessage);
        }

        return ['giftMessage' => $this->giftMessage];
    }
}
