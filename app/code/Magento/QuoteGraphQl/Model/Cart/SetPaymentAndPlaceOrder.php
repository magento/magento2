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
use Magento\Quote\Model\Quote;

/**
 * Set payment method and place order
 */
class SetPaymentAndPlaceOrder
{
    /**
     * @var SetPaymentMethodOnCart
     */
    private $setPaymentMethod;

    /**
     * @var PlaceOrder
     */
    private $placeOrder;

    /**
     * @param SetPaymentMethodOnCart $setPaymentMethod
     * @param PlaceOrder $placeOrder
     */
    public function __construct(
        SetPaymentMethodOnCart $setPaymentMethod,
        PlaceOrder $placeOrder
    ) {
        $this->setPaymentMethod = $setPaymentMethod;
        $this->placeOrder = $placeOrder;
    }

    /**
     * Set payment method and place order
     *
     * @param Quote $cart
     * @param string $maskedCartId
     * @param int $userId
     * @param array $paymentData
     * @return int
     *
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Quote $cart, string $maskedCartId, int $userId, array $paymentData): int
    {
        $this->setPaymentMethod->execute($cart, $paymentData);
        return $this->placeOrder->execute($cart, $maskedCartId, $userId);
    }
}
