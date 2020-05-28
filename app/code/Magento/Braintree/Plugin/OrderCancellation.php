<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Plugin;

use Magento\Braintree\Model\Paypal\OrderCancellationService;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider as PayPalConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Cancels an order and an authorization transaction.
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class OrderCancellation
{
    /**
     * @var OrderCancellationService
     */
    private $orderCancellationService;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param OrderCancellationService $orderCancellationService
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        OrderCancellationService $orderCancellationService,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->orderCancellationService = $orderCancellationService;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Cancels an order if an exception occurs during the order creation.
     *
     * @param CartManagementInterface $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param PaymentInterface $payment
     * @return int
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPlaceOrder(
        CartManagementInterface $subject,
        \Closure $proceed,
        $cartId,
        PaymentInterface $payment = null
    ) {
        try {
            return $proceed($cartId, $payment);
        } catch (\Exception $e) {
            $quote = $this->quoteRepository->get((int) $cartId);
            $payment = $quote->getPayment();
            $paymentCodes = [
                ConfigProvider::CODE,
                ConfigProvider::CC_VAULT_CODE,
                PayPalConfigProvider::PAYPAL_CODE,
                PayPalConfigProvider::PAYPAL_VAULT_CODE
            ];
            if (in_array($payment->getMethod(), $paymentCodes)) {
                $incrementId = $quote->getReservedOrderId();
                if ($incrementId) {
                    $this->orderCancellationService->execute($incrementId);
                }
            }

            throw $e;
        }
    }
}
