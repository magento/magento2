<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress\SetShippingAddressOnCart\MultiShipping;

/**
 * Shipping address to shipping items mapper
 */
class ShippingItemsMapper
{

    /**
     *  Converts shipping address input array into shipping items information array
     * Array structure:
     * array(
     *      $cartItemId => array(
     *          'qty'       => $qty,
     *          'address'   => $customerAddressId
     *      )
     * )
     *
     * @param array $shippingAddress
     * @return array
     */
    public function map(array $shippingAddress): array
    {
        $shippingItemsInformation = [];
        foreach ($shippingAddress['cart_items'] as $cartItem) {
            $shippingItemsInformation[] = [
                $cartItem['cart_item_id'] => [
                    'qty' => $cartItem['quantity'],
                    'address' => $shippingAddress['customer_address_id']
                ]
            ];
        }

        return $shippingItemsInformation;
    }
}
