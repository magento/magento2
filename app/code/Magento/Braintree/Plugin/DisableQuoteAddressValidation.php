<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\CartManagementInterface;

/**
 * Plugin for CartManagementInterface to disable quote address validation
 */
class DisableQuoteAddressValidation
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Disable quote address validation before place order
     *
     * @param CartManagementInterface $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param PaymentInterface|null $payment
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPlaceOrder(
        CartManagementInterface $subject,
        \Closure $proceed,
        int $cartId,
        PaymentInterface $payment = null
    ) {
        $quote = $this->quoteRepository->get($cartId);
        if ($quote->getPayment()->getMethod() == 'braintree_paypal' &&
            $quote->getCheckoutMethod() == CartManagementInterface::METHOD_GUEST) {
            $billingAddress = $quote->getBillingAddress();
            $billingAddress->setShouldIgnoreValidation(true);
            $quote->setBillingAddress($billingAddress);
        }
        return $proceed($cartId, $payment);
    }
}
