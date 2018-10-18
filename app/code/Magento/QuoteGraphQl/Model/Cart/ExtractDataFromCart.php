<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Extract data from cart
 */
class ExtractDataFromCart
{
    /**
     * Extract data from cart
     *
     * @param Quote $cart
     * @return array
     */
    public function execute(Quote $cart): array
    {
        $items = [];

        /**
         * @var QuoteItem $cartItem
         */
        foreach ($cart->getAllItems() as $cartItem) {
            $productData = $cartItem->getProduct()->getData();
            $productData['model'] = $cartItem->getProduct();

            $items[] = [
                'id' => $cartItem->getItemId(),
                'qty' => $cartItem->getQty(),
                'product' => $productData,
                'model' => $cartItem,
            ];
        }

        return [
            'items' => $items,
        ];
    }
}
