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
use Magento\StoreGraphQl\Model\Resolver\Store\Url;

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
     * @var Url
     */
    private $urlService;

    /**
     * @param PaymentRepository $paymentRepository
     * @param AdditionalDataProviderPool $additionalDataProviderPool
     * @param Url $urlService
     */
    public function __construct(
        PaymentRepository $paymentRepository,
        AdditionalDataProviderPool $additionalDataProviderPool,
        Url $urlService
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->additionalDataProviderPool = $additionalDataProviderPool;
        $this->urlService = $urlService;
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
            $this->validateUrlPaths($paymentData, $urlKeys);
            $payment = $cart->getPayment();
            foreach ($urlKeys as $urlKey) {
                if (isset($paymentData[$urlKey])) {
                    $payment->setAdditionalInformation($urlKey, $paymentData[$urlKey]);
                }
            }
            $payment->save();
        }
    }

    /**
     * Validate paths in known keys of the payment data array
     *
     * @param array $paymentData
     * @param array $urlKeys
     * @return void
     * @throws GraphQlInputException
     */
    private function validateUrlPaths(array $paymentData, array $urlKeys): void
    {
        foreach ($urlKeys as $urlKey) {
            if (!isset($paymentData[$urlKey])) {
                continue;
            }
            if (!$this->urlService->isPath($paymentData[$urlKey])) {
                throw new GraphQlInputException(__('Invalid Url.'));
            }
        }
    }
}
