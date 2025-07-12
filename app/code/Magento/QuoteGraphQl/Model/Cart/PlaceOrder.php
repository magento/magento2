<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;

/**
 * Place an order
 */
class PlaceOrder
{
    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentManagement;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @param PaymentMethodManagementInterface $paymentManagement
     * @param CartManagementInterface $cartManagement
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentManagement,
        CartManagementInterface $cartManagement
    ) {
        $this->paymentManagement = $paymentManagement;
        $this->cartManagement = $cartManagement;
    }

    /**
     * Place an order
     *
     * @param Quote $cart
     * @param string $maskedCartId
     * @param int $userId
     * @return int
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Quote $cart, string $maskedCartId, int $userId): int
    {
        $cartId = (int)$cart->getId();
        $paymentMethod = $this->paymentManagement->get($cartId);

        // Get a list of available payment methods for the cart
        $availablePaymentMethods = $this->paymentManagement->getList($cartId);
        $payment = $cart->getPayment();
        $paymentMethodCode = $payment?->getMethod();
        // Check if the selected payment method is in the available methods list
        if ($paymentMethodCode && $availablePaymentMethods) {
            $availableCodes = array_map(fn($method) => $method->getCode(), $availablePaymentMethods);
            $isPaymentMethodAvailable = in_array($paymentMethodCode, $availableCodes);
        } else {
            $isPaymentMethodAvailable = false;
        }

        if (!$isPaymentMethodAvailable) {
            throw new LocalizedException(__('The requested Payment Method is not available.'));
        }

        return (int)$this->cartManagement->placeOrder($cartId, $paymentMethod);
    }
}
