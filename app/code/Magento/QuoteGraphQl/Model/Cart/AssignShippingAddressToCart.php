<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\ShippingAddressManagementInterface;

/**
 * Assign shipping address to cart
 */
class AssignShippingAddressToCart
{
    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @param ShippingAddressManagementInterface $shippingAddressManagement
     */
    public function __construct(
        ShippingAddressManagementInterface $shippingAddressManagement
    ) {
        $this->shippingAddressManagement = $shippingAddressManagement;
    }

    /**
     * Assign shipping address to cart
     *
     * @param CartInterface $cart
     * @param QuoteAddress $shippingAddress
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(
        CartInterface $cart,
        QuoteAddress $shippingAddress
    ): void {
        try {
            $this->shippingAddressManagement->assign($cart->getId(), $shippingAddress);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}
