<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2018 Atwix (https://www.atwix.com/)
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\ShippingAddress\SetShippingAddressOnCart;

class MultiShipping
{
    /**
     * @param int $cartId
     * @param array $cartItems
     * @param int|null $customerAddressId
     * @param array|null $address
     * @return void
     */
    public function setAddress(int $cartId, array $cartItems, ?int $customerAddressId, ?array $address): void
    {
        //TODO: implement multi shipping
    }
}
