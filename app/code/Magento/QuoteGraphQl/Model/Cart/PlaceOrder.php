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
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;

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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param PaymentMethodManagementInterface $paymentManagement
     * @param CartManagementInterface $cartManagement
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentManagement,
        CartManagementInterface $cartManagement,
        ?LoggerInterface $logger = null
    ) {
        $this->paymentManagement = $paymentManagement;
        $this->cartManagement = $cartManagement;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
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
            // Log the attempt to use a disabled payment method
            $this->logger->debug(
                'Attempt to place order with disabled payment method',
                [
                    'payment_method' => $paymentMethodCode,
                    'cart_id' => $cartId,
                    'user_id' => $userId,
                    'available_methods' => $availablePaymentMethods ?
                        array_map(fn($method) => $method->getCode(), $availablePaymentMethods) : []
                ]
            );

            throw new LocalizedException(
                __('The requested Payment Method \'%1\' is not available.', $paymentMethodCode ?: 'unknown')
            );
        }

        return (int)$this->cartManagement->placeOrder($cartId, $paymentMethod);
    }
}
