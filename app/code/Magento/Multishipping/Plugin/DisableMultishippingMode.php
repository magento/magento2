<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Multishipping\Model\DisableMultishipping;

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
     * @var DisableMultishipping
     */
    private $disableMultishipping;

    /**
     * @param Cart $cart
     * @param DisableMultishipping $disableMultishipping
     */
    public function __construct(
        Cart $cart,
        DisableMultishipping $disableMultishipping
    ) {
        $this->cart = $cart;
        $this->disableMultishipping = $disableMultishipping;
    }

    /**
     * Disable multishipping
     *
     * @param Action $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(Action $subject): void
    {
        $quote = $this->cart->getQuote();
        $modChanged = $this->disableMultishipping->execute($quote);
        if ($modChanged) {
            $totalsCollectedBefore = $quote->getTotalsCollectedFlag();
            $this->cart->saveQuote();
            if (!$totalsCollectedBefore) {
                $quote->setTotalsCollectedFlag(false);
            }
        }
    }
}
