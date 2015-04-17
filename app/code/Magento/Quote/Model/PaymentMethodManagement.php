<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Framework\Exception\State\InvalidTransitionException;

class PaymentMethodManagement implements \Magento\Quote\Api\PaymentMethodManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
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
     * @var \Magento\Framework\Json\Encoder
     */
    protected $jsonEncoder;

    /**
     * @param QuoteRepository $quoteRepository
     * @param \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator
     * @param \Magento\Payment\Model\MethodList $methodList
     * @param \Magento\Framework\Json\Encoder $jsonEncoder
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator,
        \Magento\Payment\Model\MethodList $methodList,
        \Magento\Framework\Json\Encoder $jsonEncoder
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->zeroTotalValidator = $zeroTotalValidator;
        $this->methodList = $methodList;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, \Magento\Quote\Api\Data\PaymentInterface $method)
    {
        $result = [];

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $method->setChecks([
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
            \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
        ]);

        try {
            $payment = $quote->getPayment();
            $payment->importData($method->getData());
        } catch (\Magento\Payment\Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $result['error'] = __('Unable to set Payment Method');
        }

        if ($quote->isVirtual()) {
            // check if billing address is set
            if ($quote->getBillingAddress()->getCountryId() === null) {
                $result['error'] = __('Billing address is not set');
            }
            $quote->getBillingAddress()->setPaymentMethod($payment->getMethod());
        } elseif ($quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            // check if shipping address is set
            if ($quote->getShippingAddress()->getCountryId() === null) {
                $result['error'] = __('Shipping address is not set');
            }
            $quote->getShippingAddress()->setPaymentMethod($payment->getMethod());
        }

        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->quoteRepository->save($quote);

        $redirectUrl = $quote->getPayment()->getCheckoutRedirectUrl();
        if ($redirectUrl) {
            $result['redirect'] = $redirectUrl;
        }

        return  $this->jsonEncoder->encode($result);
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
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        return $this->methodList->getAvailableMethods($quote);
    }
}
