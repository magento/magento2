<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Framework\Exception\State\InvalidTransitionException;

/**
 * Class PaymentMethodManagement
 */
class PaymentMethodManagement implements \Magento\Quote\Api\PaymentMethodManagementInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Payment\Model\Checks\ZeroTotal
     */
    protected $zeroTotalValidator;

    /**
     * @var \Magento\Payment\Model\MethodList
     */
    protected $methodList;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator
     * @param \Magento\Payment\Model\MethodList $methodList
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator,
        \Magento\Payment\Model\MethodList $methodList
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->zeroTotalValidator = $zeroTotalValidator;
        $this->methodList = $methodList;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, \Magento\Quote\Api\Data\PaymentInterface $method)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);

        $method->setChecks([
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
        ]);
        $payment = $quote->getPayment();

        $data = $method->getData();
        $payment->importData($data);

        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod($payment->getMethod());
        } else {
            // check if shipping address is set
            if ($quote->getShippingAddress()->getCountryId() === null) {
                throw new InvalidTransitionException(__('Shipping address is not set'));
            }
            $quote->getShippingAddress()->setPaymentMethod($payment->getMethod());
        }
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        if (!$this->zeroTotalValidator->isApplicable($payment->getMethodInstance(), $quote)) {
            throw new InvalidTransitionException(__('The requested Payment Method is not available.'));
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
        $quote = $this->quoteRepository->get($cartId);
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
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($cartId);
        return $this->methodList->getAvailableMethods($quote);
    }
}
