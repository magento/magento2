<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Response;

use Braintree_Transaction;
use Magento\BraintreeTwo\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenFactory;

/**
 * Class PaymentDetailsHandler
 * @package Magento\BraintreeTwo\Gateway\Response
 */
class PaymentDetailsHandler implements HandlerInterface
{
    const AVS_POSTAL_RESPONSE_CODE = 'avsPostalCodeResponseCode';

    const AVS_STREET_ADDRESS_RESPONSE_CODE = 'avsStreetAddressResponseCode';

    const CVV_RESPONSE_CODE = 'cvvResponseCode';

    const PROCESSOR_AUTHORIZATION_CODE = 'processorAuthorizationCode';

    const PROCESSOR_RESPONSE_CODE = 'processorResponseCode';

    const PROCESSOR_RESPONSE_TEXT = 'processorResponseText';

    /**
     * List of additional details
     * @var array
     */
    protected $additionalInformationMapping = [
        self::AVS_POSTAL_RESPONSE_CODE,
        self::AVS_STREET_ADDRESS_RESPONSE_CODE,
        self::CVV_RESPONSE_CODE,
        self::PROCESSOR_AUTHORIZATION_CODE,
        self::PROCESSOR_RESPONSE_CODE,
        self::PROCESSOR_RESPONSE_TEXT,
    ];

    /**
     * @var PaymentTokenFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param OrderPaymentExtensionFactory $paymentExtensionFactory
     */
    public function __construct(
        PaymentTokenFactory $paymentTokenFactory,
        OrderPaymentExtensionFactory $paymentExtensionFactory
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var \Braintree_Transaction $transaction */
        $transaction = $response['object']->transaction;
        /**
         * @TODO after changes in sales module should be refactored for new interfaces
         */
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setTransactionId($transaction->id);
        $payment->setCcTransId($transaction->id);
        $payment->setLastTransId($transaction->id);
        $payment->setIsTransactionClosed(false);

        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($transaction, $payment);
        if (null !== $paymentToken) {
            $extensionAttributes = $payment->getExtensionAttributes();
            if (null === $extensionAttributes) {
                $extensionAttributes = $this->paymentExtensionFactory->create();
                $payment->setExtensionAttributes($extensionAttributes);
            }
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }

        //remove previously set payment nonce
        $payment->unsAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE);
        foreach ($this->additionalInformationMapping as $item) {
            if (!isset($transaction->$item)) {
                continue;
            }
            $payment->setAdditionalInformation($item, $transaction->$item);
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param Braintree_Transaction $transaction
     * @param Payment $payment
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken(
        \Braintree_Transaction $transaction,
        \Magento\Sales\Model\Order\Payment $payment
    ) {
        // Check token existing in gateway response
        $token = $transaction->creditCardDetails->token;
        if (empty($token)) {
            return null;
        }

        $order = $payment->getOrder();

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($token);
        $paymentToken->setCustomerId($order->getCustomerId());
        $paymentToken->setPaymentMethodCode($payment->getMethod());
        $paymentToken->setCreatedAt($order->getCreatedAt());

        return $paymentToken;
    }
}
