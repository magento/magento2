<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteManagement;

class ValidatePaymentOnPlaceOrder
{
    /**
     * @param CartRepositoryInterface $cartRepository
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private PaymentHelper $paymentHelper
    ) {
    }

    /**
     * Validate payment method
     *
     * @param QuoteManagement $subject
     * @param int $cartId
     * @param PaymentInterface|null $paymentMethod
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePlaceOrder(
        QuoteManagement $subject,
        $cartId,
        ?PaymentInterface $paymentMethod = null
    ): array {
        $quote = $this->cartRepository->getActive((int)$cartId);

        $payment = $quote->getPayment();
        $code = $paymentMethod?->getMethod() ?: $payment->getMethod();

        if (!$code) {
            return [$cartId, $paymentMethod];
        }

        $methodInstance = $this->paymentHelper->getMethodInstance($code);
        $methodInstance->setInfoInstance($payment);

        if (!$methodInstance->isAvailable($quote)) {
            throw new LocalizedException(__('The requested Payment Method is not available.'));
        }

        return [$cartId, $paymentMethod];
    }
}
