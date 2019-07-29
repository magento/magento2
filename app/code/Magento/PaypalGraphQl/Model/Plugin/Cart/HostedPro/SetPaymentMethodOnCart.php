<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Plugin\Cart\HostedPro;

use Magento\Paypal\Model\Config;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderPool;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;

/**
 * Set additionalInformation on payment for Hosted Pro method
 */
class SetPaymentMethodOnCart
{
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var AdditionalDataProviderPool
     */
    private $additionalDataProviderPool;

    /**
     * @param PaymentRepository $paymentRepository
     * @param AdditionalDataProviderPool $additionalDataProviderPool
     */
    public function __construct(
        PaymentRepository $paymentRepository,
        AdditionalDataProviderPool $additionalDataProviderPool
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->additionalDataProviderPool = $additionalDataProviderPool;
    }

    /**
     * Set payment additionalInformation
     *
     * @param \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject
     * @param mixed $result
     * @param Quote $cart
     * @param array $paymentData
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute($subject, $result, $cart, $paymentData): void
    {
        $urlKeys = ['cancel_url', 'return_url'];
        $additionalInformation = isset($paymentData['additional_data'])
            ? $this->additionalDataProviderPool->getData(Config::METHOD_HOSTEDPRO, $paymentData['additional_data'])
            : null;

        if ($additionalInformation && is_array($additionalInformation)) {
            $payment = $cart->getPayment();
            foreach ($urlKeys as $urlKey) {
                if (isset($additionalInformation[$urlKey])) {
                    $payment->setAdditionalInformation($urlKey, $additionalInformation[$urlKey]);
                }
            }
            $payment->save();
        }
    }
}
