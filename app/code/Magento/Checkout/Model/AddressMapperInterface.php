<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;

interface AddressMapperInterface
{
    /**
     * Process customer address information for same as billing
     *
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     */
    public function customerCheckoutAddressMapper(
        int $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): void;

    /**
     * Process guest address information for same as billing
     *
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     */
    public function guestCheckoutAddressMapper(
        string $cartId,
        string $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): void;
}
