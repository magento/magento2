<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Plugin\Cart\PayflowPro;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Paypal\Model\Config;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderPool;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Magento\PaypalGraphQl\Observer\PayflowProSetCcData;

/**
 * Set additionalInformation on payment for PayflowPro method
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
        array $paymentData,
        ContextInterface $context
    ): void {
        $paymentData = $this->additionalDataProviderPool->getData(Config::METHOD_PAYFLOWPRO, $paymentData);

        if (!$context->getExtensionAttributes()->getIsCustomer()
            && array_key_exists(PayflowProSetCcData::IS_ACTIVE_PAYMENT_TOKEN_ENABLER, $paymentData)) {
            $payment = $cart->getPayment();
            $payment->unsAdditionalInformation(PayflowProSetCcData::IS_ACTIVE_PAYMENT_TOKEN_ENABLER);
            $payment->save();
        }
    }
}
