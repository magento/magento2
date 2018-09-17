<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Hydrator;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Cart Hydrator class
 *
 * {@inheritdoc}
 */
class CartHydrator
{
    /**
     * Hydrate cart to plain array
     *
     * @param CartInterface|Quote $cart
     *
     * @return array
     */
    public function hydrate(CartInterface $cart): array
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
