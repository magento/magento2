<?php

declare(strict_types=1);

namespace Magento\Wishlist\Helper;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Helper\AbstractHelper;

class FromCartRemoveItem extends AbstractHelper
{
    public function removeItemFromQuote(Cart $cart, int $itemId): void
    {
        $cart->getQuote()->removeItem($itemId);
        $cart->save();
    }
}
