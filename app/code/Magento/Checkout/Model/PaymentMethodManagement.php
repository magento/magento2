<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\Framework\Exception\State\InvalidTransitionException;

class PaymentMethodManagement implements \Magento\Checkout\Api\PaymentMethodManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Checkout\Service\V1\Data\Cart\PaymentMethod\Builder
     */
    protected $paymentMethodBuilder;

    /**
     * @var \Magento\Payment\Model\Checks\ZeroTotal
     */
    protected $zeroTotalValidator;

    /**
     * @var \Magento\Payment\Model\MethodList
     */
    protected $methodList;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator
     * @param \Magento\Payment\Model\MethodList $methodList
     * @param \Magento\Checkout\Api\Data\PaymentMethodDataBuilder $paymentMethodBuilder
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator,
        \Magento\Payment\Model\MethodList $methodList,
        \Magento\Checkout\Api\Data\PaymentMethodDataBuilder $paymentMethodBuilder
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->paymentMethodBuilder = $paymentMethodBuilder;
        $this->zeroTotalValidator = $zeroTotalValidator;
        $this->methodList = $methodList;
    }

    /**
     * {@inheritdoc}
     */
    public function set(\Magento\Checkout\Api\Data\PaymentInterface $method, $cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $method->setChecks([
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
        ]);
        $payment = $quote->getPayment();
        $payment->importData($method->getData());

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

        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
        return $quote->getPayment()->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $payment = $quote->getPayment();
        if (!$payment->getId()) {
            return null;
        }
        return $payment;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        $output = [];
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        foreach ($this->methodList->getAvailableMethods($quote) as $method) {
            $output[] = $this->paymentMethodBuilder
                ->setTitle($method->getTitle())
                ->setCode($method->getCode())
                ->create();
        }
        return $output;
    }
}
