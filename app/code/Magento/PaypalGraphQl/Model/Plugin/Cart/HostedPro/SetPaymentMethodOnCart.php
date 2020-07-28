<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Plugin\Cart\HostedPro;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
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
     * Set redirect URL paths on payment additionalInformation
     *
     * @param \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject
     * @param mixed $result
     * @param Quote $cart
     * @param array $paymentData
     * @return void
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject,
        $result,
        Quote $cart,
        array $paymentData
    ): void {
        $paymentData = $this->additionalDataProviderPool->getData(Config::METHOD_HOSTEDPRO, $paymentData);

        if (!empty($paymentData)) {
            $urlKeys = ['cancel_url', 'return_url'];
            $payment = $cart->getPayment();
            foreach ($urlKeys as $urlKey) {
                if (isset($paymentData[$urlKey])) {
                    $payment->setAdditionalInformation($urlKey, $paymentData[$urlKey]);
                }
            }
            $payment->save();
        }
    }
}
