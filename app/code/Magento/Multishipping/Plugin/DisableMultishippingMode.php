<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;

/**
 * Turns Off Multishipping mode for Quote.
 */
class DisableMultishippingMode
{
    /**
     * @var Cart
     */
    private $cart;

    /**
     * @param Cart $cart
     */
    public function __construct(
        Cart $cart
    ) {
        $this->cart = $cart;
    }

    /**
     * Disable multishipping
     *
     * @param Action $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(Action $subject)
    {
        $quote = $this->cart->getQuote();
        if ($quote->getIsMultiShipping()) {
            $quote->setIsMultiShipping(0);
            $extensionAttributes = $quote->getExtensionAttributes();
            if ($extensionAttributes && $extensionAttributes->getShippingAssignments()) {
                $extensionAttributes->setShippingAssignments([]);
            }
            $this->cart->saveQuote();
        }
    }
}
