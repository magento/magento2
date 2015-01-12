<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\PaymentMethod;

use Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Builder;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Payment\Model\Checks\ZeroTotal;
use Magento\Sales\Model\QuoteRepository;

/**
 * Payment method write service object.
 */
class WriteService implements WriteServiceInterface
{
    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Payment method builder.
     *
     * @var Builder
     */
    protected $paymentMethodBuilder;

    /**
     * Zero total validator.
     *
     * @var ZeroTotal
     */
    protected $zeroTotalValidator;

    /**
     * Constructs a payment method write service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param Builder $paymentMethodBuilder Payment method builder.
     * @param ZeroTotal $zeroTotalValidator Zero total validator.
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Builder $paymentMethodBuilder,
        ZeroTotal $zeroTotalValidator
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->paymentMethodBuilder = $paymentMethodBuilder;
        $this->zeroTotalValidator = $zeroTotalValidator;
    }

    /**
     * {@inheritDoc}
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\PaymentMethod $method The payment method.
     * @param int $cartId The cart ID.
     * @return int Payment method ID.
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException The billing or shipping address is not set, or the specified payment method is not available.
     */
    public function set(\Magento\Checkout\Service\V1\Data\Cart\PaymentMethod $method, $cartId)
    {
        $quote = $this->quoteRepository->getActive($cartId);

        $payment = $this->paymentMethodBuilder->build($method, $quote);
        if ($quote->isVirtual()) {
            // check if billing address is set
            if (is_null($quote->getBillingAddress()->getCountryId())) {
                throw new InvalidTransitionException('Billing address is not set');
            }
            $quote->getBillingAddress()->setPaymentMethod($payment->getMethod());
        } else {
            // check if shipping address is set
            if (is_null($quote->getShippingAddress()->getCountryId())) {
                throw new InvalidTransitionException('Shipping address is not set');
            }
            $quote->getShippingAddress()->setPaymentMethod($payment->getMethod());
        }
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        if (!$this->zeroTotalValidator->isApplicable($payment->getMethodInstance(), $quote)) {
            throw new InvalidTransitionException('The requested Payment Method is not available.');
        }

        $quote->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save();

        return $quote->getPayment()->getId();
    }
}
